<?php

namespace Siesta\Rating\Domain;

use Siesta\Shared\Id\Id;

class Rating
{
    public function __construct(
        public readonly Id $userId,
        public readonly Id $movieId,
        public readonly RatingScore $score
    )
    {
    }
}
