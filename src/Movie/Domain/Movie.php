<?php

namespace Siesta\Movie\Domain;

use Siesta\Shared\Id\Id;
use Siesta\Vote\Domain\VoteCollection;

class Movie implements \JsonSerializable
{
    private VoteCollection $voteCollection;

    public function __construct(
        public readonly Id      $id,
        public readonly string  $title,
        public readonly string  $poster,
        public readonly string  $trailer_id,
        public readonly int     $duration,
        public readonly string  $summary,
        public readonly ?string $link,
        public readonly ?string $comments,
        public readonly int     $film_festival_id,
        public readonly ?string $alias,
        public readonly ?string $section,
        public readonly array   $sessions
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


    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id->id,
            'title' => $this->title,
            'poster' => $this->poster,
            'trailer' => $this->trailer_id,
            'duration' => $this->duration,
            'summary' => $this->summary,
            'link' => $this->link,
            'comments' => $this->comments,
            'film_festival_id' => $this->film_festival_id,
            'alias' => $this->alias,
            'section' => $this->section,
            'sessions' => array_map(fn(Session $session) => [
                'location' => $session->location,
                'init_date' => $session->initDate->__toString(),
                'end_date' => $session->initDate->__toString(),
                'movies' => $session->movies
            ], $this->sessions)
        ];
    }
}