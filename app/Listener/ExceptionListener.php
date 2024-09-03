<?php

namespace Siesta\App\Listener;

use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Exception\ValueNotValid;
use Siesta\User\Domain\InvalidLoginData;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $status = $this->status($exception);
        $response = new JsonResponse([
            'error' => $exception->getMessage(),
            'reason' => $this->reason($exception),
        ], $status);

        $event->setResponse($response);
    }

    private function status(Throwable $e): int
    {
        if ($e instanceof ValueNotValid) {
            return Response::HTTP_BAD_REQUEST;
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return Response::HTTP_METHOD_NOT_ALLOWED;
        }
        if ($e instanceof DataNotFound) {
            return Response::HTTP_NOT_FOUND;
        }
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function reason(Throwable $e): string
    {
        if (
            $e instanceof ValueNotValid
        ) {
            return 'http.bad.request';
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return 'method.not.allowed';
        }

        if ($e instanceof DataNotFound) {
            return 'resource.not.found';
        }

        return 'internal.server.error';
    }

}