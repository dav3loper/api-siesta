<?php

namespace Siesta\Agent\Domain;

class UserProfile
{
    public function __construct(public readonly RatedMovieCollection $ratedMovies)
    {
    }
}
