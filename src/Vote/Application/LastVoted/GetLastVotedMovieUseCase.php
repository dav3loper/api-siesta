<?php

namespace Siesta\Vote\Application\LastVoted;

use Siesta\Vote\Domain\VoteRepository;

class GetLastVotedMovieUseCase
{

    public function __construct(private readonly VoteRepository $voteRepository)
    {
    }

    public function execute(GetLastVotedMovieUseCaseRequest $request): LastMovieVotedResponse
    {
        $vote = $this->voteRepository->getLastVotedMovieForUserAndFilmFestival($request->userId, $request->filmFestivalId);
        return new LastMovieVotedResponse($vote->movieId);
    }

}