<?php

namespace Siesta\Vote\Application\LastVoted;

use Siesta\Movie\Domain\MovieRepository;
use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;
use Siesta\Vote\Domain\VoteRepository;

class GetNextMovieToVoteUseCase
{

    public function __construct(private readonly VoteRepository $voteRepository, private readonly MovieRepository $movieRepository)
    {
    }

    /**
     * @throws DataNotFound
     * @throws InternalError
     */
    public function execute(GetNextMovieToVoteUseCaseRequest $request): NextMovieToVoteResponse
    {

        $lastMovieVoted = $this->getLastVotedMovieId($request);
        $nextMovie = $this->movieRepository->getNextMovie($lastMovieVoted, $request->filmFestivalId);
        return new NextMovieToVoteResponse($nextMovie->id);
    }

    public function getLastVotedMovieId(GetNextMovieToVoteUseCaseRequest $request): Id
    {
        try {
            $lastVoted = $this->voteRepository->getLastVotedMovieForUserAndFilmFestival($request->userId, $request->filmFestivalId);
            return $lastVoted->movieId;
        }catch (DataNotFound) {
            return new Id('0');
        }
    }

}