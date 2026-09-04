<?php

namespace Siesta\Agent\Domain;

use Siesta\Shared\Score\Score;

class RatedMovie
{
    public function __construct(
        public readonly string $title,
        public readonly ?Score $voteScore,
        public readonly ?int $ratingScore
    )
    {
    }
}
