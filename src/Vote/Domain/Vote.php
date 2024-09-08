<?php

namespace Siesta\Vote\Domain;

use Siesta\Shared\Id\Id;
use Siesta\Shared\Score\Score;

class Vote
{
    public function __construct(
        public readonly Id $userId,
        public readonly Score $score,
        public readonly Id $movieId,
        public readonly ?Id $groupId
    )
    {
    }
}