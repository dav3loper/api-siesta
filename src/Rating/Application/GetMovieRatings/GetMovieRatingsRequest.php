<?php

namespace Siesta\Rating\Application\GetMovieRatings;

class GetMovieRatingsRequest
{
    public function __construct(
        public readonly string $filmFestivalId,
        public readonly string $userId,
    )
    {
    }
}
