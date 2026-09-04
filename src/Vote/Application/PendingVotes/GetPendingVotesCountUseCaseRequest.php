<?php

namespace Siesta\Vote\Application\PendingVotes;

use Siesta\Shared\Id\Id;

class GetPendingVotesCountUseCaseRequest
{
    public function __construct(public readonly Id $filmFestivalId, public readonly Id $userId)
    {
    }

}
