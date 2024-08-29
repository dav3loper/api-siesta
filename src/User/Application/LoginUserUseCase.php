<?php

namespace Siesta\User\Application;

use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Exception\ValueNotValid;
use Siesta\Shared\ValueObject\Email;
use Siesta\Shared\ValueObject\Password;
use Siesta\User\Domain\InvalidLoginData;
use Siesta\User\Domain\TokenService;
use Siesta\User\Domain\User;
use Siesta\User\Domain\UserRepository;

class LoginUserUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TokenService $tokenService
    )
    {
    }

    /**
     * @throws InvalidLoginData
     */
    public function execute(Email $email, Password $password): LoginResponse
    {
        $user = $this->getUser($email);

        $this->checkPassword($password, $user);
        $token = $this->getToken($user);

        return new LoginResponse($token);
    }

    /**
     * @throws InvalidLoginData
     * @throws InternalError
     */
    public function getUser(Email $email): User
    {
        try {
            $user = $this->userRepository->findByEmail($email);
        } catch (DataNotFound|ValueNotValid) {
            throw new InvalidLoginData();
        }
        return $user;
    }

    /**
     * @param Password $password
     * @param mixed $user
     * @return void
     * @throws InvalidLoginData
     */
    public function checkPassword(Password $password, User $user): void
    {

        $isSame = $user->password->compareWith($password);
        if (!$isSame) {
            throw new InvalidLoginData();
        }
    }

    private function getToken(User $user): string
    {
        return $this->tokenService->encode([
            'user_id' => $user->id->id,
            'user_name' => $user->name,
            'group_id' => $user->group->id->id,
            'group_name' => $user->group->name
        ]);

    }

}