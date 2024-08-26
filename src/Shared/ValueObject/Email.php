<?php

namespace Siesta\Shared\ValueObject;

use Siesta\Shared\Exception\ValueNotValid;

class Email
{

    private string $email;

    /**
     * @throws ValueNotValid
     */
    public function __construct(string $email)
    {
        $this->validate($email);
        $this->email = strtolower(trim($email));
    }


    /**
     * @throws ValueNotValid
     */
    private function validate(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValueNotValid();
        }
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }


}