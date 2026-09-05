<?php

namespace Siesta\Rating\Domain;

use Siesta\Shared\Id\Id;

/**
 * A movie of an edition as the rating screen needs it: enough of the catalog to paint the
 * row, plus how it is rated. The Rating context keeps its own read model of a movie instead
 * of reaching into the Movie one.
 */
class MovieRating
{
    public function __construct(
        public readonly Id $movieId,
        public readonly string $title,
        public readonly string $poster,
        public readonly ?string $section,
        public readonly int $filmFestivalId,
        public readonly RatingSummary $summary,
    )
    {
    }
}
