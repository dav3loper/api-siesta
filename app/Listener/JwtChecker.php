<?php

namespace Siesta\App\Listener;

use Siesta\User\Domain\TokenService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

class JwtChecker
{

    public function __construct(private readonly TokenService $tokenService, private readonly array $routes)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {

        $request = $event->getRequest();
        $partialUri = $request->getRequestUri();
        $needsAuthorization = !in_array($partialUri, $this->routes);
        if (!$needsAuthorization || !$event->isMainRequest()) {
            return;
        }
        $authToken = $this->getHeaderAuthorization($request);
        if (!$authToken) {
            $event->setResponse(new JsonResponse([], 401));
            return;
        }

        try {
            $params = $this->decodeToken($authToken);
        } catch (Throwable) {
            // A token that cannot be read is a failed authentication, not a broken server:
            // until now it escaped as the 500 of an uncaught JWT exception.
            $event->setResponse(new JsonResponse([], 401));
            return;
        }

        $request->headers->add($params);
    }

    private function getHeaderAuthorization(Request $request): string
    {
        $authorization = $request->headers->get('authorization');
        if (empty($authorization) || !str_contains($authorization, 'Bearer')) {
            return '';
        }

        return str_replace('Bearer ', '', $authorization);
    }


    private function decodeToken(string $token): array
    {
        $parameters = $this->tokenService->decode($token);
        return json_decode((string)json_encode($parameters), true);
    }

}