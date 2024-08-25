<?php

namespace Siesta\Shared\Date;

use DateTime;

class Date
{
    private DateTime $date;

    public function __construct(string $dateString)
    {
        $this->date = new DateTime($dateString);
    }

    public function __toString(): string
    {
        return $this->date->format('Y-m-d H:i:s');
    }

}