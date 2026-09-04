<?php

namespace Siesta\Agent\Domain;

use Siesta\Shared\Collection\Collection;

class RatedMovieCollection extends Collection
{
    protected function type(): string
    {
        return RatedMovie::class;
    }
}
