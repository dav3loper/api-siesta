<?php

namespace Siesta\Movie\Infrastructure;

use Siesta\Movie\Domain\Movie;
use Siesta\Movie\Domain\MovieRepository;
use Siesta\Shared\Id\Id;

class DoctrineMovieRepository implements MovieRepository
{

    public function __construct()
    {
    }

    public function getById(string $id): Movie
    {
        return new Movie(new Id('1'));
    }
}