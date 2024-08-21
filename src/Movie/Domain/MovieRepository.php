<?php

namespace Siesta\Movie\Domain;

interface MovieRepository
{

    public function getById(string $id): Movie;
}