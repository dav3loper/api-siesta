<?php

namespace Siesta\Movie\Domain;

use Siesta\Shared\Date\Date;

class Session
{

    public function __construct(
        public readonly string $location,
        public readonly Date $initDate,
        public readonly Date $endDate,
        public readonly string $movies
    )
    {
    }
}