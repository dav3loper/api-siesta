<?php

namespace Siesta\Vote\Application\PendingVotes;

class PendingVotesCountResponse
{

    public function __construct(public readonly int $count)
    {
    }

}
