<?php

namespace Siesta\Tests\Fixtures\Movie;

use Siesta\Movie\Domain\Movie;
use Siesta\Shared\Id\Id;
use Siesta\Tests\Fixtures\Mother;

class MovieMother extends Mother
{
    private Id $id;

    public static function create(): MovieMother
    {
        return new self();
    }

    public function random(): MovieMother
    {
        $this->id = new Id($this->faker->numberBetween(0, 2000));

        return $this;

    }

    public function build(): Movie
    {
        return new Movie($this->id);
    }
}