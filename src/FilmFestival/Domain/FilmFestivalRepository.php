<?php

namespace Siesta\FilmFestival\Domain;

interface FilmFestivalRepository
{
    /**
     * @return FilmFestival[]
     */
    public function getAll(): array;
}
