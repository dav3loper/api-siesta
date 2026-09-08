<?php

namespace Siesta\Movie\Application\UpdateMovieMedia;

class UpdateMovieMediaRequest
{
    /**
     * A media field left out of the request keeps its current value, so "not sent" and
     * "sent as null" have to travel apart.
     */
    public function __construct(
        public readonly string $movieId,
        public readonly bool $changesPoster,
        public readonly ?string $poster,
        public readonly bool $changesTrailer,
        public readonly ?string $trailer,
    )
    {
    }
}
