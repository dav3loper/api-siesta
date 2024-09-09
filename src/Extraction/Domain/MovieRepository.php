<?php

namespace Siesta\Extraction\Domain;

interface MovieRepository
{

    public function store(Movie $movie): void;
}