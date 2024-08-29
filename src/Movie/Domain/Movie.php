<?php

namespace Siesta\Movie\Domain;

use Siesta\Shared\Id\Id;
use Siesta\Vote\Domain\VoteCollection;

class Movie
{
    private VoteCollection $voteCollection;

    public function __construct(
        public readonly Id     $id,
        public readonly string $title,
        public readonly string $poster,
        public readonly string $trailer_id,
        public readonly int    $duration,
        public readonly string $summary,
        public readonly ?string $link,
        public readonly ?string $comments,
        public readonly int    $film_festival_id,
        public readonly ?string $alias,
    )
    {
    }

    public function setVoteCollection(VoteCollection $voteCollection): void
    {
        $this->voteCollection = $voteCollection;
    }

    public function getVoteCollection(): VoteCollection
    {
        return $this->voteCollection;
    }


}