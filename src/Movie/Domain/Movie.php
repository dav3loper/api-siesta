<?php

namespace Siesta\Movie\Domain;

use Siesta\Shared\Id\Id;

class Movie
{

    public function __construct(public readonly Id $id)
    {
    }

}