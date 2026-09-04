<?php

namespace Siesta\Extraction\Domain;

interface SynopsisFinder
{
    public function findByTitle(string $title, ?int $year): string;
}
