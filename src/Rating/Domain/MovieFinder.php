<?php

namespace Siesta\Rating\Domain;

use Siesta\Shared\Id\Id;

/**
 * The rating table has no foreign key, so this is what keeps a rating from being stored
 * for a movie that does not exist.
 */
interface MovieFinder
{
    public function exists(Id $movieId): bool;
}
