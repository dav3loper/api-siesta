<?php

namespace Siesta\User\Application;

class LoginResponse implements \JsonSerializable
{
    public function __construct(private readonly string $token)
    {
    }


    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}