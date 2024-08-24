<?php

namespace Siesta\Shared\Id;

class Id
{
    public function __construct(public readonly string $id)
    {
    }

    public function __toString(): string
    {
        return $this->id;
    }
}