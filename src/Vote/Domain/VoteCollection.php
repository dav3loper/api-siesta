<?php

namespace Siesta\Vote\Domain;

use Siesta\Shared\Collection\Collection;

class VoteCollection extends Collection
{

    protected function type(): string
    {
        return Vote::class;
    }
}