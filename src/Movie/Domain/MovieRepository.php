<?php

namespace Siesta\Movie\Domain;

use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;

interface MovieRepository
{

    public function getById(string $id): Movie;

    /**
     * @throws DataNotFound
     * @throws InternalError
     */
    public function getNextMovie(Id $movieId, Id $filmFestivalId): Movie;
}