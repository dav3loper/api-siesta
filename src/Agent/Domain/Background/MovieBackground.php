<?php

namespace Siesta\Agent\Domain\Background;

/**
 * What is known about a movie beyond the festival catalog: who made it and what else
 * they have done. It comes from an external source, so every field can be missing.
 */
class MovieBackground
{
    /**
     * @param string[] $genres
     * @param string[] $mainCast
     * @param string[] $otherMoviesByDirector
     */
    public function __construct(
        public readonly string $title,
        public readonly ?int $year,
        public readonly ?string $director,
        public readonly array $genres,
        public readonly array $mainCast,
        public readonly array $otherMoviesByDirector,
        public readonly ?float $audienceScore,
    )
    {
    }
}
