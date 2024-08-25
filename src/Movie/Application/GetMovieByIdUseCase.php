<?php

namespace Siesta\Movie\Application;

use Siesta\Movie\Domain\MovieRepository;

class GetMovieByIdUseCase
{

    public function __construct(
        private readonly MovieRepository $movieRepository)
    {
    }

    public function execute(mixed $id): MovieResponse
    {
        $movieWithVotes = $this->movieRepository->getById($id);

        return new MovieResponse($movieWithVotes);

    }
}