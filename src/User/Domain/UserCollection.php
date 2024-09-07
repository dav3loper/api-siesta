<?php

namespace Siesta\User\Domain;

use Siesta\Shared\Collection\Collection;

class UserCollection extends Collection
{

    protected function type(): string
    {
        return User::class;
    }
}