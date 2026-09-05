<?php

namespace Siesta\Rating\Application\GetMovieRatings;

use Siesta\Rating\Domain\RatingRepository;
use Siesta\Shared\Id\Id;

class GetMovieRatingsUseCase
{
    public function __construct(private readonly RatingRepository $ratingRepository)
    {
    }

    public function execute(GetMovieRatingsRequest $request): MovieRatingListResponse
    {
        $movieRatings = $this->ratingRepository->findAllByFilmFestivalId(
            new Id($request->filmFestivalId),
            new Id($request->userId)
        );

        return new MovieRatingListResponse($movieRatings);
    }
}
