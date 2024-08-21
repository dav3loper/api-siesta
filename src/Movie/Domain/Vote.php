<?php

namespace Siesta\Movie\Domain;

use Siesta\Shared\Id\Id;
use Siesta\Shared\Score\Score;

class Vote
{
    public function __construct(public readonly Id $userId, public readonly Score $score)
    {
    }
}