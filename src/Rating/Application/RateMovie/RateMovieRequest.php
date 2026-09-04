<?php

namespace Siesta\Rating\Application\RateMovie;

class RateMovieRequest
{
    public function __construct(
        public readonly string $userId,
        public readonly string $movieId,
        public readonly int $score,
    )
    {
    }
}
