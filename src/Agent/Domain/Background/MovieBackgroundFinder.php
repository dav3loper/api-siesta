<?php

namespace Siesta\Agent\Domain\Background;

interface MovieBackgroundFinder
{
    /**
     * @return MovieBackground|null null when the source knows nothing about that movie
     */
    public function findByTitle(string $title, ?int $year): ?MovieBackground;
}
