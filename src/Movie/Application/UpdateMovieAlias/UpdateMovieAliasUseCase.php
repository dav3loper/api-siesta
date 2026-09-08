<?php

namespace Siesta\Movie\Application\UpdateMovieAlias;

use Siesta\Movie\Application\MovieResponse;
use Siesta\Movie\Domain\MovieRepository;
use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;

class UpdateMovieAliasUseCase
{
    public function __construct(private readonly MovieRepository $movieRepository)
    {
    }

    /**
     * @throws DataNotFound
     * @throws InternalError
     */
    public function execute(UpdateMovieAliasRequest $updateMovieAliasRequest): MovieResponse
    {
        $updatedMovie = $this->movieRepository
            ->getById($updateMovieAliasRequest->movieId)
            ->withAlias($updateMovieAliasRequest->alias);

        $this->movieRepository->updateAlias($updatedMovie);

        return new MovieResponse($updatedMovie);
    }
}
