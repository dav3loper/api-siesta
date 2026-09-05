<?php

namespace Siesta\Rating\Application\GetMovieRatings;

use JsonSerializable;
use Siesta\Rating\Application\RatingSummaryResponse;
use Siesta\Rating\Domain\MovieRating;
use Siesta\Rating\Domain\MovieRatingCollection;

class MovieRatingListResponse implements JsonSerializable
{
    public function __construct(private readonly MovieRatingCollection $movieRatings)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function jsonSerialize(): array
    {
        return array_map(
            fn (MovieRating $movieRating): array => array_merge(
                [
                    'id' => (int)$movieRating->movieId->id,
                    'title' => $movieRating->title,
                    'poster' => $movieRating->poster,
                    'section' => $movieRating->section,
                    'film_festival_id' => $movieRating->filmFestivalId,
                ],
                (new RatingSummaryResponse($movieRating->summary))->jsonSerialize()
            ),
            $this->movieRatings->items()
        );
    }
}
