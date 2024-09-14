<?php

namespace Siesta\Movie\Application;

use Siesta\Movie\Domain\Movie;

class MovieResponse implements \JsonSerializable
{
    public function __construct(
        public readonly Movie $movie
    )
    {
    }


    public function jsonSerialize(): mixed
    {
        return $this->movie->jsonSerialize();
    }
}