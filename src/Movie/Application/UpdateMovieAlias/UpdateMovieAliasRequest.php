<?php

namespace Siesta\Movie\Application\UpdateMovieAlias;

class UpdateMovieAliasRequest
{
    public function __construct(
        public readonly string $movieId,
        public readonly ?string $alias,
    )
    {
    }
}
