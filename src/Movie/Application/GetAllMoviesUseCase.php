<?php

namespace Siesta\Movie\Application;

use Siesta\Movie\Domain\MovieRepository;

class GetAllMoviesUseCase
{
    public function __construct(private readonly MovieRepository $movieRepository)
    {
    }

    public function execute(int $filmFestivalId, int $groupId): MovieListResponse
    {
        $movieList = $this->movieRepository->getAllByFilmFestivalId($filmFestivalId, $groupId);

        return new MovieListResponse($movieList);

    }

}