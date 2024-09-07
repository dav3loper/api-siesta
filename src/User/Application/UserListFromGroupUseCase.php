<?php

namespace Siesta\User\Application;

use Siesta\Shared\Id\Id;
use Siesta\User\Domain\UserRepository;

class UserListFromGroupUseCase
{

    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function execute(Id $groupId): UserResponse
    {
        $userCollection = $this->userRepository->findByGroupId($groupId);
        return new UserResponse($groupId, $userCollection);

    }
}