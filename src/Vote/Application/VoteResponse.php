<?php

namespace Siesta\Vote\Application;

use Siesta\Vote\Domain\Vote;
use Siesta\Vote\Domain\VoteCollection;

class VoteResponse implements \JsonSerializable
{

    public function __construct(public readonly VoteCollection $voteCollection)
    {
    }

    public function jsonSerialize(): array
    {
        return [
            "votes" => array_map(fn(Vote $vote) =>
                [
                    "user_id" => $vote->userId,
                    "movie_id" => $vote->movieId,
                    "score" => $vote->score,
                ]
            , $this->voteCollection->items())
        ];
    }
}