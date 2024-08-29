<?php

namespace Siesta\Vote\Application;

use Siesta\Shared\Id\Id;

class GetVotesForMovieRequest
{
    public function __construct(public readonly Id $movieId, public readonly ?Id $groupId)
    {
    }

}