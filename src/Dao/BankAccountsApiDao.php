<?php

namespace App\Dao;

use App\Dao\Abstract\AbstractDao;
use App\Dao\Interface\CustomersApiDaoInterface;
use App\Exception\ServiceException;
use App\Model\Abstract\AbstractModel;
use App\Model\Interface\RequestModelInterface;
use App\Model\Request\ApiRequest;
use App\Model\Request\CustomerModel;
use App\Model\Request\IncomingRequestModel;
use App\Model\Response\OutgoingResponseModel;
use App\Serializer\ApiRequestSerializer;
use App\Service\CustomerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BankAccountsApiDao extends AbstractDao implements CustomersApiDaoInterface
{
    public function __construct(
        private CustomerService $service,
        private ApiRequestSerializer $serializer
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        return $this->convertToIncomingRequestModelAndCallService($request, AbstractModel::CRUD_KEY_CREATE);
    }

    public function read(Request $request): JsonResponse
    {
        return $this->convertToIncomingRequestModelAndCallService($request, AbstractModel::CRUD_KEY_READ);
    }

    public function readOne(Request $request): JsonResponse
    {
        return $this->convertToIncomingRequestModelAndCallService($request, AbstractModel::CRUD_KEY_READ_ONE);
    }

    public function update(Request $request): JsonResponse
    {
        return $this->convertToIncomingRequestModelAndCallService($request, AbstractModel::CRUD_KEY_UPDATE);
    }

    public function delete(Request $request): JsonResponse
    {
        return $this->convertToIncomingRequestModelAndCallService($request, AbstractModel::CRUD_KEY_DELETE);
    }

    /**
     * Note: This shortcut method means the IDE can't find the call to the service. Duplicate this method for
     * each method above to see the link. This just tidies things up a bit and prevents code duplication.
     */
    private function convertToIncomingRequestModelAndCallService(Request $request, string $crudKey): JsonResponse
    {
        try {
            $incomingRequestModel = $this->convertHttpRequestIntoIncomingRequestModel($request, $crudKey);
            $methodName = lcfirst($crudKey);
            $outgoingResponse = $this->service->$methodName($incomingRequestModel);
        } catch (ServiceException $exception) {
            // @TODO Create a response model for the exception - shortcut is to just use the event listener.
            // return $this->createServiceErrorResponseModel(exception: $exception);
            throw $exception; //@TODO this is a shortcut.
        }

        return $this->serializeOutgoingResponseModelIntoJsonResponse($outgoingResponse);
    }

    /**
     * The Http Request has everything, but we want to standardise what goes through to the service layer
     * so the API could be swapped out with another technology or messaging protocol later.
     */
    private function convertHttpRequestIntoIncomingRequestModel(Request $request, string $crudKey): IncomingRequestModel
    {
        return new IncomingRequestModel(
            routeParameters: $request->attributes->get(self::ROUTER_ROUTE_PARAMS_KEY),
            requestParameters: $request->query->all(),
            crudKey: $crudKey,
            model: $this->deserializeRequestBodyIntoCustomerModelForCrudKey($crudKey, $request->getContent())
        );
    }

    private function deserializeRequestBodyIntoCustomerModelForCrudKey(string $crudKey, ?string $requestBody): ?RequestModelInterface
    {
        $customerModel = $this->getRequestModelClassNameFromCrudKey($crudKey);

        // Only Create and Update CRUD operations require a model to be passed to the service.
        if ($customerModel === null) {
            return null;
        }

        return $this->serializer->deserialize(
            data: $requestBody,
            type: $customerModel,
            format: self::FORMAT_JSON
        );
    }

    /**
     * Only Create and Update CRUD operations require a model class.
     */
    private function getRequestModelClassNameFromCrudKey(string $crudKey): ?string
    {
        switch ($crudKey) {
            case AbstractModel::CRUD_KEY_CREATE:
            case AbstractModel::CRUD_KEY_UPDATE:
                return CustomerModel::class;
            default:
                return null;
        }
    }

    private function serializeOutgoingResponseModelIntoJsonResponse(OutgoingResponseModel $outgoingResponse): JsonResponse
    {

        // @TODO This is likely to need more than just standard JSON.
        $json = "";
        if ($outgoingResponse->getModel() !== null) {
            $json = $this->serializer->serialize($outgoingResponse->getModel(), self::FORMAT_JSON);
        }

        //@TODO transform service status codes to HTTP response codes - shortcut - they are the same.
        $statusCode = $outgoingResponse->getStatusCode() ?? JsonResponse::HTTP_OK;

        return new JsonResponse(
            data: $json,
            status: $statusCode,
            json: true
        );
    }
}
