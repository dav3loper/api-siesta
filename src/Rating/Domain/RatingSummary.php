<?php

namespace Siesta\Rating\Domain;

/**
 * How a movie stands in the ratings: what everybody gave it and what this user gave it.
 * Both the listing and the answer to a new rating are built from this, so the two cannot
 * disagree on the average.
 */
class RatingSummary
{
    private const PRECISION = 1;

    public readonly ?float $average;

    public function __construct(?float $average, public readonly int $count, public readonly ?int $userScore)
    {
        $this->average = $average === null ? null : round($average, self::PRECISION);
    }

    public static function withoutRatings(): self
    {
        return new self(null, 0, null);
    }
}
