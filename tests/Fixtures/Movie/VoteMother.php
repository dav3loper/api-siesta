<?php

namespace Siesta\Tests\Fixtures\Movie;

use Siesta\Movie\Domain\Vote;
use Siesta\Movie\Domain\VoteCollection;
use Siesta\Shared\Id\Id;
use Siesta\Shared\Score\Score;
use Siesta\Tests\Fixtures\Mother;

class VoteMother extends Mother
{
    private array $voteList;

    public static function create(): VoteMother
    {
        return new self();
    }

    public function random(): VoteMother
    {
        $iterations = $this->faker->numberBetween(1, 5);
        $voteList = [];
        for ($i = 0; $i < $iterations; $i++) {
            $voteList[] = new Vote(
                new Id($this->faker->numberBetween(1, 1000)),
                $this->faker->randomElement(Score::cases())
            );
        }
        $this->voteList = $voteList;
        return $this;

    }

    public function build(): VoteCollection
    {
        return new VoteCollection($this->voteList);

    }
}