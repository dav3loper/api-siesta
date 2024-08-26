<?php

namespace Siesta\User\Domain;

use Siesta\Shared\Id\Id;
use Siesta\Shared\ValueObject\Email;
use Siesta\Shared\ValueObject\Password;

class User
{
    public function __construct(
        public readonly Id       $id,
        public readonly Email    $email,
        public readonly string   $name,
        public readonly Password $password,
        public readonly ?Group $group
    )
    {
    }

}