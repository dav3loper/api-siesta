<?php

namespace Siesta\Extraction\Application;

use Siesta\Extraction\Domain\Movie;
use Siesta\Movie\Domain\MovieRepository;

class StoreMovieUseCase
{
    public function __construct(private readonly MovieRepository $movieRepository)
    {
    }

    public function execute(Movie $movie): void
    {


    }

}