<?php

namespace Siesta\Movie\Application;

use Siesta\Movie\Domain\Movie;

class MovieListResponse
{
    /**
     * @param Movie[] $movieList
     */
    public function __construct(public readonly array $movieList)
    {
    }

}