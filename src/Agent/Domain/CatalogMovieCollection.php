<?php

namespace Siesta\Agent\Domain;

use Siesta\Shared\Collection\Collection;

class CatalogMovieCollection extends Collection
{
    protected function type(): string
    {
        return CatalogMovie::class;
    }
}
