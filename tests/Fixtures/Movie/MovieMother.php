<?php

namespace Siesta\Tests\Fixtures\Movie;

use Siesta\Movie\Domain\Movie;
use Siesta\Movie\Domain\Session;
use Siesta\Shared\Date\Date;
use Siesta\Shared\Id\Id;
use Siesta\Tests\Fixtures\Mother;

class MovieMother extends Mother
{
    private Id $id;
    private string $title;
    private string $poster;
    private string $trailer_id;
    private int $duration;
    private string $summary;
    private ?string $link;
    private string $comments;
    private int $film_festival_id;
    private ?string $alias;
    private ?string $section;
    /** @var Session[] */
    private array $sessions;
    private bool $poster_locked;
    private bool $trailer_locked;

    public static function create(): MovieMother
    {
        return new self();
    }

    public function random(): MovieMother
    {
        $this->id = new Id($this->faker->numberBetween(0, 2000));
        $this->title = $this->faker->word;
        $this->poster = $this->faker->imageUrl();
        $this->trailer_id = $this->faker->uuid;
        $this->duration = $this->faker->numberBetween(0, 100);
        $this->summary = $this->faker->sentence();
        $this->link = $this->faker->url();
        $this->comments = $this->faker->text();
        $this->film_festival_id = $this->faker->numberBetween(1, 50);
        $this->alias = $this->faker->name();
        $this->section = $this->faker->word;
        $this->sessions = [
            new Session($this->faker->city, new Date('now'), new Date('+2 hours'), $this->faker->word),
        ];
        $this->poster_locked = false;
        $this->trailer_locked = false;

        return $this;

    }

    public function build(): Movie
    {
        return new Movie(
            $this->id,
            $this->title,
            $this->poster,
            $this->trailer_id,
            $this->duration,
            $this->summary,
            $this->link,
            $this->comments,
            $this->film_festival_id,
            $this->alias,
            $this->section,
            $this->sessions,
            $this->poster_locked,
            $this->trailer_locked,
        );
    }
}