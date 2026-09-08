<?php

namespace Siesta\Movie\Application\UpdateMovieMedia;

use Siesta\Movie\Application\MovieResponse;
use Siesta\Movie\Domain\MovieRepository;
use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;

class UpdateMovieMediaUseCase
{
    public function __construct(private readonly MovieRepository $movieRepository)
    {
    }

    /**
     * @throws DataNotFound
     * @throws InternalError
     */
    public function execute(UpdateMovieMediaRequest $updateMovieMediaRequest): MovieResponse
    {
        $updatedMovie = $this->movieRepository->getById($updateMovieMediaRequest->movieId);

        if ($updateMovieMediaRequest->changesPoster) {
            $updatedMovie = $updatedMovie->withPoster($updateMovieMediaRequest->poster);
        }
        if ($updateMovieMediaRequest->changesTrailer) {
            $updatedMovie = $updatedMovie->withTrailer($updateMovieMediaRequest->trailer);
        }
        $this->movieRepository->updateMedia($updatedMovie);

        return new MovieResponse($updatedMovie);
    }
}
