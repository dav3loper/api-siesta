<?php

namespace Siesta\Shared\ValueObject;

class Password
{
    private function __construct(private readonly string $password)
    {

    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public static function clear(string $passwordWithoutEncoding): Password
    {
        return new self($passwordWithoutEncoding);
    }

    public static function encoded(string $passwordCyphered): Password
    {
        return new self($passwordCyphered);

    }

    public function compareWith(Password $clearPassword): bool
    {
        return password_verify($clearPassword->getPassword(), $this->password);

    }

}