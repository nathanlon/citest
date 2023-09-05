<?php

namespace App\Service;

use App\Entity\Customer;
use App\Exception\ServiceException;
use App\Model\Abstract\AbstractModel;
use App\Model\Request\IncomingRequestModel;
use App\Model\Response\CustomerCreatedModel;
use App\Model\Response\CustomerListModel;
use App\Model\Response\CustomerModel;
use App\Model\Response\OutgoingResponseModel;
use App\Repository\CustomerRepository;
use App\Service\Abstract\AbstractService;
use App\Service\Interface\CustomerServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomerService extends AbstractService implements CustomerServiceInterface
{
    public function __construct(
        protected ValidatorInterface $validator,
        protected LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private CustomerRepository $repository,
    ) {
    }

    public function create(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        try {
            $this->validateModel($incomingRequestModel);

            $customerModel = $incomingRequestModel->getModel();

            $customerEntity = new Customer();
            $customerEntity->setFirstName($customerModel->getFirstName());
            $customerEntity->setLastName($customerModel->getLastName());
            $customerEntity->setSsn($customerModel->getSsn());

            $this->entityManager->persist($customerEntity);
            $this->entityManager->flush();

            //when created, simply returns the id of the created customer.
            $customerCreatedModel = new CustomerCreatedModel();
            $customerCreatedModel->setId($customerEntity->getId());

            $outgoingResponse = new OutgoingResponseModel();
            $outgoingResponse->setStatusCode(self::STATUS_CODE_CREATED);
            $outgoingResponse->setModel($customerCreatedModel);
        } catch (\Exception $exception) {
            throw new ServiceException(
                message: 'Unable to create customer. An error occurred while saving.',
                previous: $exception
            );
        }

        return $outgoingResponse;
    }

    public function read(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        try {
            //@TODO Could put these into the read model so as to abstract these from the service.
            $limit = $incomingRequestModel->getRequestParameters()[AbstractModel::REQUEST_PARAM_LIMIT] ?? self::DEFAULT_LIMIT;
            $offset = $incomingRequestModel->getRequestParameters()[AbstractModel::REQUEST_PARAM_OFFSET] ?? self::DEFAULT_OFFSET;

            //@TODO Would put these into queries on the Repository.
            $customerEntities = $this->repository->findBy(
                criteria: [],
                orderBy: [],
                limit: $limit,
                offset: $offset
            );

            $customerModels = [];
            foreach ($customerEntities as $customerEntity) {
                $customerModel = $this->createCustomerModelFromEntity($customerEntity);
                $customerModels[] = $customerModel;
            }

            $customerListModel = new CustomerListModel();
            $customerListModel->setCustomers($customerModels);

            $outgoingResponse = new OutgoingResponseModel();
            $outgoingResponse->setStatusCode(self::STATUS_CODE_OK);
            $outgoingResponse->setModel($customerListModel);
        } catch (\Exception $exception) {
            throw new ServiceException(
                message: 'Unable to read customers. An error occurred while retrieving.',
                previous: $exception
            );
        }

        return $outgoingResponse;
    }

    public function readOne(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        $statusCode = self::STATUS_CODE_OK;
        try {
            $customerEntity = $this->getCustomerEntityFromModel($incomingRequestModel);
            $model = $this->createCustomerModelFromEntity($customerEntity);
        } catch (ServiceException $exception) {
            //it's OK to have nothing, just modify the status code to 404.
            $statusCode = self::STATUS_CODE_NOT_FOUND;
            $model = null;
        }

        $outgoingResponse = new OutgoingResponseModel();
        $outgoingResponse->setStatusCode($statusCode);
        $outgoingResponse->setModel($model);

        return $outgoingResponse;
    }

    public function update(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        $customerEntity = $this->getCustomerEntityFromModel($incomingRequestModel);

        $model = $incomingRequestModel->getModel();

        if (!empty($model->getFirstName())) {
            $customerEntity->setFirstName($model->getFirstName());
        }
        if (!empty($model->getLastName())) {
            $customerEntity->setLastName($model->getLastName());
        }
        if (!empty($model->getSsn())) {
            $customerEntity->setSsn($model->getSsn());
        }

        $this->entityManager->flush();

        $outgoingResponse = new OutgoingResponseModel();
        $outgoingResponse->setStatusCode(self::STATUS_CODE_NO_CONTENT);

        return $outgoingResponse;
    }

    public function delete(IncomingRequestModel $incomingRequestModel): OutgoingResponseModel
    {
        $customerEntity = $this->getCustomerEntityFromModel($incomingRequestModel);

        $this->entityManager->remove($customerEntity);
        $this->entityManager->flush();

        $outgoingResponse = new OutgoingResponseModel();
        $outgoingResponse->setStatusCode(self::STATUS_CODE_NO_CONTENT);

        return $outgoingResponse;
    }

    private function createCustomerModelFromEntity(Customer $customerEntity): CustomerModel
    {
        $customerModel = new CustomerModel();
        $customerModel->setId($customerEntity->getId());
        $customerModel->setFirstName($customerEntity->getFirstName());
        $customerModel->setLastName($customerEntity->getLastName());
        $customerModel->setSsn($customerEntity->getSsn());

        return $customerModel;
    }

    /**
     * @throws ServiceException
     */
    public function getCustomerEntityFromModel(IncomingRequestModel $incomingRequestModel): Customer
    {
        $id = $incomingRequestModel->getRouteParameters()['id'];

        $customerEntity = $this->repository->find($id);

        if ($customerEntity === null) {
            throw new ServiceException(sprintf("Customer with id %d could not be found.", $id));
        }
        return $customerEntity;
    }
}
