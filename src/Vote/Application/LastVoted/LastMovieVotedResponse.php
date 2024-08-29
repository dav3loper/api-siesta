<?php

namespace Siesta\Vote\Application\LastVoted;

use Siesta\Shared\Id\Id;

class LastMovieVotedResponse
{

    public function __construct(public readonly Id $movie)
    {
    }

}