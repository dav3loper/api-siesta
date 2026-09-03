<?php

namespace Siesta\FilmFestival\Application;

use Siesta\FilmFestival\Domain\FilmFestival;

class FilmFestivalListResponse
{
    /**
     * @param FilmFestival[] $filmFestivalList
     */
    public function __construct(public readonly array $filmFestivalList)
    {
    }
}
