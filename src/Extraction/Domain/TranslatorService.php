<?php

namespace Siesta\Extraction\Domain;

interface TranslatorService
{
    public function translateToSpanish(string $text): string;
}
