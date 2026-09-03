<?php

namespace Siesta\Extraction\Domain;

interface MovieListFinder
{
    /**
     * @return MovieListEntry[]
     */
    public function findAll(string $url): array;
}
