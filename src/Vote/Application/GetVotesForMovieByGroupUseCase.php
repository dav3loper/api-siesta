<?php

namespace Siesta\Vote\Application;

use Siesta\Shared\Id\Id;
use Siesta\Vote\Domain\VoteRepository;

class GetVotesForMovieByGroupUseCase
{


    public function __construct(private readonly VoteRepository $voteRepository)
    {
    }

    public function execute(GetVotesForMovieRequest $request): VoteResponse
    {
        $voteListCollection = $this->voteRepository->getAllByMovieId($request->movieId, $request->groupId);

        return new VoteResponse($voteListCollection);

    }
}