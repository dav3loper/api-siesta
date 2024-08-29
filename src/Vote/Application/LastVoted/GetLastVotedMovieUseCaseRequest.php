<?php

namespace Siesta\Vote\Application\LastVoted;

use Siesta\Shared\Id\Id;

class GetLastVotedMovieUseCaseRequest
{
    public function __construct(public readonly Id $filmFestivalId, public readonly Id $userId)
    {
    }

}