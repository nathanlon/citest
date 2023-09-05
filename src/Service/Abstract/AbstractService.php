<?php

namespace App\Service\Abstract;

use App\Exception\ServiceException;
use App\Model\Request\IncomingRequestModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractService
{
    protected const VALIDATION_FAILED_ERROR_CODE = 3;

    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_OFFSET = 0;

    //Note: This would generally not be a 201 as that is API specific. There would be further mapping.
    public const STATUS_CODE_CREATED = Response::HTTP_CREATED;
    public const STATUS_CODE_OK = Response::HTTP_OK;
    public const STATUS_CODE_NOT_FOUND = Response::HTTP_NOT_FOUND;
    public const STATUS_CODE_NO_CONTENT = Response::HTTP_NO_CONTENT;

    protected ValidatorInterface $validator;
    protected LoggerInterface $logger;

    protected function validateModel(IncomingRequestModel $incomingRequestModel): void
    {
        if ($incomingRequestModel->getModel() === null) {
            return;
        }

        $errors = $this->validator->validate($incomingRequestModel->getModel());
        if (count($errors) > 0) {
            $errorsString = $this->convertErrorsToString($errors);

            throw new ServiceException(
                message: 'There were validation errors: ' . $errorsString,
                code: self::VALIDATION_FAILED_ERROR_CODE
            );
        }
    }

    protected function convertErrorsToString(ConstraintViolationListInterface $errors): string
    {
        $errorsString = '';
        foreach ($errors as $error) {
            $errorsString .= $error->getMessage() . ' ';
        }
        return $errorsString;
    }
}
