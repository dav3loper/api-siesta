<?php

namespace Siesta\User\Domain;

use Siesta\Shared\Id\Id;

class Group
{

    public function __construct(public readonly Id $id, public readonly string $name)
    {
    }

}