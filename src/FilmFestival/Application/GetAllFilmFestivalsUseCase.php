<?php

namespace Siesta\FilmFestival\Application;

use Siesta\FilmFestival\Domain\FilmFestivalRepository;

class GetAllFilmFestivalsUseCase
{
    public function __construct(private readonly FilmFestivalRepository $filmFestivalRepository)
    {
    }

    public function execute(): FilmFestivalListResponse
    {
        return new FilmFestivalListResponse($this->filmFestivalRepository->getAll());
    }
}
