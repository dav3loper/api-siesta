<?php

namespace Siesta\Agent\Domain;

class CatalogMovie
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $section,
        public readonly ?int $duration,
        public readonly ?string $summary,
    )
    {
    }
}
