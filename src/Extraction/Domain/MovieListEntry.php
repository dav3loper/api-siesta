<?php

namespace Siesta\Extraction\Domain;

class MovieListEntry
{
    public function __construct(
        public readonly string $title,
        public readonly ?int   $year,
        public readonly string $link,
    )
    {
    }
}
