<?php

namespace Siesta\Movie\Domain;

use Siesta\Shared\Id\Id;
use Siesta\Shared\Score\Score;
use Siesta\Vote\Domain\Vote;
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
        $this->voteCollection = new VoteCollection([]);
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
            'votes' => array_map(fn(Vote $vote) => [
                'score' => $vote->score,
                'user_id' => $vote->userId,
                'user_name' => $this->getInitial($vote->userId, $vote->score),
            ], $this->voteCollection->items()),
            'sessions' => array_map(fn(Session $session) => [
                'location' => $session->location,
                'init_date' => $session->initDate->__toString(),
                'end_date' => $session->initDate->__toString(),
                'movies' => $session->movies
            ], $this->sessions)
        ];
    }

    private function getInitial(Id $userId, Score $score): string
    {
        if($userId->id == '1' && $score->value > 0){ //s
            return $score->value == 1 ? 's' : 'S';

        }elseif($userId->id == '11' && $score->value > 0){//u
            return $score->value == 1 ? 'u' : 'U';

        }elseif($userId->id == '21' && $score->value > 0){ //d
            return $score->value == 1 ? 'd' : 'D';

        }elseif($userId->id == '31' && $score->value > 0){//m
            return $score->value == 1 ? 'm' : 'M';

        }elseif($userId->id == '41' && $score->value > 0){//l
            return $score->value == 1 ? 'l' : 'L';
        }
        return $score->value >0 ? 'x' :'';

    }
}