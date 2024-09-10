<?php

namespace Siesta\Extraction\Domain;

interface FinderVideoService
{

    public function findByText(string $text): string;
}