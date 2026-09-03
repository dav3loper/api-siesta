<?php

namespace Siesta\FilmFestival\Domain;

use JsonSerializable;
use Siesta\Shared\Date\Date;
use Siesta\Shared\Id\Id;

class FilmFestival implements JsonSerializable
{
    public function __construct(
        public readonly Id     $id,
        public readonly int    $editionNumber,
        public readonly string $name,
        public readonly Date   $startDate,
        public readonly Date   $endDate,
    )
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id->id,
            'edition_number' => $this->editionNumber,
            'name' => $this->name,
            'start_date' => $this->startDate->__toString(),
            'end_date' => $this->endDate->__toString(),
        ];
    }
}
