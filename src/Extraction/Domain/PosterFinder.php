<?php

namespace Siesta\Extraction\Domain;

interface PosterFinder
{
    public function findByTitle(string $title, ?int $year): string;
}
