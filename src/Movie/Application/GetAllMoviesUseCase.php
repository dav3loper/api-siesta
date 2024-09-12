<?php

namespace Siesta\Movie\Application;

use Siesta\Movie\Domain\MovieRepository;

class GetAllMoviesUseCase
{
    public function __construct(private readonly MovieRepository $movieRepository)
    {
    }

    public function execute(int $filmFestivalId): MovieListResponse
    {
        $movieList = $this->movieRepository->getAllByFilmFestivalId($filmFestivalId);

        return new MovieListResponse($movieList);

    }

}