<?php

namespace Siesta\Agent\Domain;

use Siesta\Shared\Exception\ValueNotValid;

class UserMessage
{
    private const MAX_LENGTH = 1000;

    private string $message;

    /**
     * @throws ValueNotValid
     */
    public function __construct(string $message)
    {
        $message = trim($message);
        $this->validate($message);
        $this->message = $message;
    }

    /**
     * @throws ValueNotValid
     */
    private function validate(string $message): void
    {
        if ($message === '') {
            throw new ValueNotValid('El mensaje no puede estar vacío');
        }

        if (mb_strlen($message) > self::MAX_LENGTH) {
            throw new ValueNotValid("El mensaje no puede superar los " . self::MAX_LENGTH . " caracteres");
        }
    }

    public function value(): string
    {
        return $this->message;
    }
}
