<?php

namespace Siesta\Movie\Application;

use Siesta\Movie\Domain\Movie;

class MovieResponse
{
    public function __construct(
        public readonly Movie $movie
    )
    {
    }

}