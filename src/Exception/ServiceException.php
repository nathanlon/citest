<?php

namespace App\Exception;

class ServiceException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, int $internalCode, ?Throwable $previous = null)
    {
        $message = sprintf("%s (Error code: %d)", $message, $internalCode);

        parent::__construct($message, $code, $previous);
    }
}
