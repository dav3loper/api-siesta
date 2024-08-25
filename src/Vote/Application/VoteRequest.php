<?php

namespace Siesta\Vote\Application;

class VoteRequest
{
    public function __construct(
        public readonly string $userId,
        public readonly string $movieId,
        public readonly ?int $score,
    )
    {
    }

}