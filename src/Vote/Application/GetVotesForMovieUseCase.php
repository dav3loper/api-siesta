<?php

namespace Siesta\Vote\Application;

use Siesta\Shared\Id\Id;
use Siesta\Vote\Domain\VoteRepository;

class GetVotesForMovieUseCase
{


    public function __construct(private readonly VoteRepository $voteRepository)
    {
    }

    public function execute(Id $id): VoteResponse
    {
        $voteListCollection = $this->voteRepository->getAllByMovieId($id);

        return new VoteResponse($voteListCollection);

    }
}