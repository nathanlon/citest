<?php

namespace App\Service\Abstract;

use App\Exception\ServiceException;
use App\Model\Abstract\AbstractModel;
use App\Model\Interface\RequestModelInterface;
use App\Model\Request\IncomingRequestModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractService
{
    protected const VALIDATION_FAILED_ERROR_CODE = 3;
    protected const CUSTOMER_ID_NOT_FOUND_ERROR_CODE = 4;
    protected const BANK_ACCOUNT_ID_NOT_FOUND_ERROR_CODE = 5;
    protected const DATABASE_ERROR_CODE = 6;
    protected const UNKNOWN_ERROR_CODE = 7;
    protected const DUPLICATE_ERROR_CODE = 8;
    protected const PREFERRED_BANK_ACCOUNT_ALREADY_SET_ERROR_CODE = 9;

    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_OFFSET = 0;

    //Note: This would generally not be a 201 as that is API specific. There would be further mapping.
    public const STATUS_CODE_CREATED = Response::HTTP_CREATED;
    public const STATUS_CODE_OK = Response::HTTP_OK;
    public const STATUS_CODE_NOT_FOUND = Response::HTTP_NOT_FOUND;
    public const STATUS_CODE_NO_CONTENT = Response::HTTP_NO_CONTENT;
    public const STATUS_UNPROCESSABLE_ENTITY = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected ValidatorInterface $validator;
    protected LoggerInterface $logger;

    protected function validateModel(RequestModelInterface $model)
    {
        $errors = $this->validator->validate($model);
        if (count($errors) > 0) {
            $errorsString = $this->convertErrorsToString($errors);

            throw new ServiceException(
                message: 'There were validation errors: ' . $errorsString,
                code: self::STATUS_UNPROCESSABLE_ENTITY,
                internalCode: self::VALIDATION_FAILED_ERROR_CODE
            );
        }
    }

    protected function validateIncomingRequestModel(IncomingRequestModel $incomingRequestModel): void
    {
        if ($incomingRequestModel->getModel() === null) {
            return;
        }

        $this->validateModel($incomingRequestModel->getModel());
    }

    protected function convertErrorsToString(ConstraintViolationListInterface $errors): string
    {
        $errorsString = '';
        foreach ($errors as $error) {
            $errorsString .= $error->getMessage() . ' ';
        }
        return $errorsString;
    }

    /**
     * @throws ServiceException
     */
    protected function getRequiredModel(IncomingRequestModel $incomingRequestModel): RequestModelInterface
    {
        $model = $incomingRequestModel->getModel();

        if ($model === null) {
            throw new ServiceException(
                message: 'There was no body for the request',
                code: self::STATUS_UNPROCESSABLE_ENTITY,
                internalCode: self::VALIDATION_FAILED_ERROR_CODE
            );
        }
        return $model;
    }

    protected function getCustomerId(IncomingRequestModel $incomingRequestModel): ?int
    {
        return $incomingRequestModel->getRouteParameters()[AbstractModel::ROUTE_PARAM_CUSTOMER_ID] ?? null;
    }

    protected function getLimit(IncomingRequestModel $incomingRequestModel): int
    {
        return $incomingRequestModel->getRequestParameters()[AbstractModel::REQUEST_PARAM_LIMIT] ?? self::DEFAULT_LIMIT;
    }

    protected function getOffset(IncomingRequestModel $incomingRequestModel): int
    {
        return $incomingRequestModel->getRequestParameters()[AbstractModel::REQUEST_PARAM_OFFSET] ?? self::DEFAULT_OFFSET;
    }
}
