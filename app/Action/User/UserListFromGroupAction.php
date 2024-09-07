<?php

namespace Siesta\App\Action\User;

use Siesta\App\Action\BaseAction;
use Siesta\Shared\Id\Id;
use Siesta\User\Application\UserListFromGroupUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserListFromGroupAction extends BaseAction
{


    public function __construct(private readonly UserListFromGroupUseCase $userListFromGroupUseCase)
    {
    }

    public function __invoke(Request $request): Response
    {
        $groupId = $request->headers->get('Group-Id');

        $response = $this->userListFromGroupUseCase->execute(new Id($groupId));
        return new JsonResponse($response);
    }
}