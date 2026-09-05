<?php

namespace Siesta\Rating\Application\RateMovie;

use Siesta\Rating\Application\RatingSummaryResponse;
use Siesta\Rating\Domain\MovieFinder;
use Siesta\Rating\Domain\Rating;
use Siesta\Rating\Domain\RatingRepository;
use Siesta\Rating\Domain\RatingScore;
use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\ValueNotValid;
use Siesta\Shared\Id\Id;

class RateMovieUseCase
{
    public function __construct(
        private readonly RatingRepository $ratingRepository,
        private readonly MovieFinder $movieFinder,
    )
    {
    }

    /**
     * @throws ValueNotValid
     * @throws DataNotFound
     */
    public function execute(RateMovieRequest $rateMovieRequest): RatingSummaryResponse
    {
        $movieId = new Id($rateMovieRequest->movieId);
        if (!$this->movieFinder->exists($movieId)) {
            throw new DataNotFound("Movie with {$rateMovieRequest->movieId} not found");
        }

        $userId = new Id($rateMovieRequest->userId);
        $rating = new Rating(
            $userId,
            $movieId,
            new RatingScore($rateMovieRequest->score),
        );
        $this->ratingRepository->upsert($rating);

        // Returned so the caller can repaint the row it just rated without reloading the list.
        return new RatingSummaryResponse($this->ratingRepository->summaryOf($movieId, $userId));
    }
}
