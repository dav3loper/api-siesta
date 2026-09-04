<?php

namespace Siesta\Vote\Application\PendingVotes;

use Siesta\Shared\Exception\InternalError;
use Siesta\Vote\Domain\VoteRepository;

class GetPendingVotesCountUseCase
{

    public function __construct(private readonly VoteRepository $voteRepository)
    {
    }

    /**
     * @throws InternalError
     */
    public function execute(GetPendingVotesCountUseCaseRequest $request): PendingVotesCountResponse
    {
        $count = $this->voteRepository->countMoviesLeftToVote($request->userId, $request->filmFestivalId);

        return new PendingVotesCountResponse($count);
    }

}
