<?php

namespace Siesta\Rating\Domain;

interface RatingRepository
{
    public function upsert(Rating $rating): void;
}
