<?php

namespace Siesta\Vote\Application\Vote;

use Siesta\Shared\Id\Id;
use Siesta\Shared\Score\Score;
use Siesta\Vote\Domain\Vote;
use Siesta\Vote\Domain\VoteRepository;

class VoteForMovieUseCase
{
    public function __construct(private readonly VoteRepository $voteRepository)
    {
    }

    public function execute(VoteRequest $voteRequest): void
    {
        $vote = new Vote(
            new Id($voteRequest->userId),
            $voteRequest->score !== null ? Score::from($voteRequest->score) : Score::NOT_YET,
            new Id($voteRequest->movieId),
            $voteRequest->groupId ? new Id($voteRequest->groupId) : null,
        );
        $this->voteRepository->upsert($vote);
    }

}