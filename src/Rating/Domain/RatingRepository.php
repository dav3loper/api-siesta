<?php

namespace Siesta\Rating\Domain;

use Siesta\Shared\Id\Id;

interface RatingRepository
{
    public function upsert(Rating $rating): void;

    /**
     * Every movie of the edition, rated or not, ordered by title.
     */
    public function findAllByFilmFestivalId(Id $filmFestivalId, Id $userId): MovieRatingCollection;

    public function summaryOf(Id $movieId, Id $userId): RatingSummary;
}
