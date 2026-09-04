<?php

namespace Siesta\Rating\Application\RateMovie;

use Siesta\Rating\Domain\Rating;
use Siesta\Rating\Domain\RatingRepository;
use Siesta\Rating\Domain\RatingScore;
use Siesta\Shared\Exception\ValueNotValid;
use Siesta\Shared\Id\Id;

class RateMovieUseCase
{
    public function __construct(private readonly RatingRepository $ratingRepository)
    {
    }

    /**
     * @throws ValueNotValid
     */
    public function execute(RateMovieRequest $rateMovieRequest): void
    {
        $rating = new Rating(
            new Id($rateMovieRequest->userId),
            new Id($rateMovieRequest->movieId),
            new RatingScore($rateMovieRequest->score),
        );
        $this->ratingRepository->upsert($rating);
    }
}
