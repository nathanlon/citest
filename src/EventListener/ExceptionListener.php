<?php

namespace App\EventListener;

use App\Exception\ServiceException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[AsEventListener]
final class ExceptionListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $this->logger->error("Exception occurred in ExceptionListener: {$exception->getMessage()}");

        $response = new JsonResponse([
            'error' => [
                'code' => $exception->getCode(),
                'message' => "Malformed request. Error has been logged.",
                'realMessage' => $exception->getMessage() //@TODO This should be hidden.
            ]
        ]);

        if ($exception instanceof HttpExceptionInterface) {
            $exceptionHeaders = $exception->getHeaders();
            if (isset($exceptionHeaders['Content-Type']) && $exceptionHeaders['Content-Type'] !== 'application/json') {
                return;
            }
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exceptionHeaders);
        } elseif ($exception instanceof ServiceException) {
            $response->setStatusCode($exception->getCode());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}
