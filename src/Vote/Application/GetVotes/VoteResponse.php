<?php

namespace Siesta\Vote\Application\GetVotes;

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
                    "user_id" => $vote->userId->id,
                    "movie_id" => $vote->movieId->id,
                    "score" => $vote->score->value,
                ]
            , $this->voteCollection->items())
        ];
    }
}