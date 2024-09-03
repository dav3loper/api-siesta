<?php

namespace Siesta\App\Action\User;

use Siesta\App\Action\BaseAction;
use Siesta\Shared\ValueObject\Email;
use Siesta\Shared\ValueObject\Password;
use Siesta\User\Application\LoginUserUseCase;
use Siesta\User\Domain\InvalidLoginData;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class LoginUserAction extends BaseAction
{

    const MANDATORY_PARAMS = ['email', 'password'];

    public function __construct(private readonly LoginUserUseCase $loginUserUseCase)
    {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $loginData = $this->getParameters($request);

            $token = $this->loginUserUseCase->execute(
                new Email($loginData['email']), Password::clear($loginData['password'])
            );
            return new JsonResponse([
                'token' => $token,
            ]);
        }catch (InvalidLoginData){
            return new JsonResponse('Invalid login data', 403);
        }
    }

    private function getParameters(Request $request): array
    {
        $loginData = $request->toArray();
        $missingParams = array_filter(self::MANDATORY_PARAMS, fn($param) => !in_array($param, array_keys($loginData)));
        if(!empty($missingParams)) {
            throw new MissingMandatoryParametersException();
        }
        return $loginData;
    }

}