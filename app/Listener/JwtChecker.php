<?php

namespace Siesta\App\Listener;

use Siesta\User\Domain\TokenService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

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
        if(!$needsAuthorization){
            return;
        }
        //TODO: cambiar por try/catch
        $authToken = $this->getHeaderAuthorization($request);
        if ($event->isMainRequest() && $authToken) {
            $params = $this->decodeToken($authToken);
            $request->headers->add($params);
        } else {
            $event->setResponse(new JsonResponse([], 401));
        }


    }
    private function getHeaderAuthorization(Request $request): string
    {
        $authorization = $request->headers->get('authorization');
        if(empty($authorization) || !str_contains($authorization, 'Bearer')){
            return '';
        }

        return str_replace('Bearer ', '', $authorization);
    }


    private function decodeToken(string $token): array
    {
        $parameters = $this->tokenService->decode($token);
        return json_decode((string) json_encode($parameters), true);
    }

}