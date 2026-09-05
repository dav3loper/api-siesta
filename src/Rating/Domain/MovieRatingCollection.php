<?php

namespace Siesta\Rating\Domain;

use Siesta\Shared\Collection\Collection;

class MovieRatingCollection extends Collection
{
    protected function type(): string
    {
        return MovieRating::class;
    }
}
