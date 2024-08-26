<?php

namespace Siesta\User\Domain;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenService
{
    public function __construct(private readonly string $secretKey, private readonly string $algorithm)
    {
    }

    public function encode(array $data): string
    {
        return JWT::encode($data, $this->secretKey, $this->algorithm);
    }

    public function decode(string $token): array
    {
        $decodeTokenObj = JWT::decode($token, new Key($this->secretKey, $this->algorithm));

        return json_decode((string)json_encode($decodeTokenObj), true);
    }


}