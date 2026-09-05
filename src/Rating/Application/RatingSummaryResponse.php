<?php

namespace Siesta\Rating\Application;

use JsonSerializable;
use Siesta\Rating\Domain\RatingSummary;

/**
 * The three rating fields, shaped once: the listing and the answer to a new rating speak
 * the same vocabulary because they both serialize through here.
 */
class RatingSummaryResponse implements JsonSerializable
{
    public function __construct(private readonly RatingSummary $summary)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'average_rating' => $this->summary->average,
            'ratings_count' => $this->summary->count,
            'user_rating' => $this->summary->userScore,
        ];
    }
}
