<?php

namespace Siesta\Extraction\Domain;

use Siesta\Shared\Id\Id;
use Siesta\Vote\Domain\VoteCollection;

class Movie
{

    public function __construct(
        public readonly string $title,
        public readonly string $poster,
        public readonly string $trailer_id,
        public readonly int    $duration,
        public readonly string $summary,
        public readonly ?string $link,
        public readonly int    $film_festival_id,
        public readonly string $section,
        public array $sessions
    )
    {
    }

}