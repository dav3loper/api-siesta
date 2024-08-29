<?php

namespace Siesta\Vote\Domain;

use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;

interface VoteRepository
{

    /**
     * @throws InternalError
     */
    public function getAllByMovieId(Id $id, ?Id $groupId): VoteCollection;

    public function upsert(Vote $vote): void;

    public function getLastVotedMovieForUserAndFilmFestival(Id $userId, Id $filmFestivalId): Vote;
}