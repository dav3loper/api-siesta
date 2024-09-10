<?php

namespace Siesta\Vote\Application\LastVoted;

use Siesta\Shared\Id\Id;

class NextMovieToVoteResponse
{

    public function __construct(public readonly Id $movie)
    {
    }

}