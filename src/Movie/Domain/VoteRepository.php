<?php

namespace Siesta\Movie\Domain;

use Siesta\Shared\Id\Id;

interface VoteRepository
{

    public function getAllByMovieId(Id $id): VoteCollection;
}