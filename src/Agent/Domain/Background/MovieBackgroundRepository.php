<?php

namespace Siesta\Agent\Domain\Background;

/**
 * Local copy of what the external source answered, so the same movie is only looked up once.
 * It is keyed by the title that was searched, not by the title the source came back with:
 * both can differ and the next question will arrive with the searched one again.
 */
interface MovieBackgroundRepository
{
    public function findBySearchedTitle(string $searchedTitle): ?MovieBackground;

    public function save(string $searchedTitle, MovieBackground $background): void;
}
