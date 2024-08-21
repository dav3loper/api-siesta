<?php

namespace Siesta\Movie\Application;

use Siesta\Movie\Domain\Movie;
use Siesta\Movie\Domain\VoteCollection;

class MovieWithVotesResponse
{
    public function __construct(
        public readonly Movie $movie,
        public readonly VoteCollection $voteCollection
    )
    {
    }

}