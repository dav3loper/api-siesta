<?php

namespace Siesta\Movie\Application;

use Siesta\Movie\Domain\MovieRepository;
use Siesta\Movie\Domain\VoteRepository;

class GetMovieByIdUseCase
{

    public function __construct(
        private readonly MovieRepository $movieRepository,
        private readonly VoteRepository $voteRepository)
    {
    }

    public function execute(mixed $id)
    {
        $movie = $this->movieRepository->getById($id);
        $voteForMovie = $this->voteRepository->getAllByMovieId($movie->id);

        return new MovieWithVotesResponse($movie, $voteForMovie);

    }
}