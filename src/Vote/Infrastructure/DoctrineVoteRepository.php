<?php

namespace Siesta\Vote\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Shared\Date\Date;
use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;
use Siesta\Shared\Score\Score;
use Siesta\Vote\Domain\Vote;
use Siesta\Vote\Domain\VoteCollection;
use Siesta\Vote\Domain\VoteRepository;
use Throwable;

class DoctrineVoteRepository implements VoteRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws InternalError
     */
    public function getAllByMovieId(Id $id, ?Id $groupId): VoteCollection
    {
        try {
            $query = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('vote')
                ->where('movie_id=:id')
                ->setParameter('id', $id)
                ->setParameter('groupId', $groupId);
            if ($groupId) {
                $query = $query->andWhere('group_id=:groupId')
                    ->setParameter('groupId', $groupId);
            }
            $data = $query->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }
        return $this->fromDataToVoteList($data);
    }

    private function fromDataToVoteList(array $data): VoteCollection
    {
        $voteCollection = new VoteCollection([]);
        foreach ($data as $dataOfVote) {
            $voteCollection->add($this->fromDataToVote($dataOfVote));

        }
        return $voteCollection;
    }

    public function upsert(Vote $vote): void
    {
        $exitingVote = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('vote')
            ->where('user_id=:userId')
            ->andWhere('movie_id=:movieId')
            ->setParameter('userId', $vote->userId)
            ->setParameter('movieId', $vote->movieId)
            ->fetchOne();
        if ($exitingVote) {
            $this->connection->update('vote', [
                'score' => $vote->score->value,
                'updated_at' => new Date('now')
            ], [
                'id' => $exitingVote
            ]);
            return;
        }

        $this->connection->insert('vote', [
            'user_id' => $vote->userId,
            'movie_id' => $vote->movieId,
            'score' => $vote->score->value,
            'group_id' => $vote->groupId,
            'created_at' => new Date('now'),
            'updated_at' => new Date('now')
        ]);

    }

    /**
     * @throws DataNotFound
     * @throws InternalError
     */
    public function getLastVotedMovieForUserAndFilmFestival(Id $userId, Id $filmFestivalId): Vote
    {
        try {
            $data = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('vote', 'v')
                ->innerJoin('v', 'movie', 'm', 'm.id = v.movie_id')
                ->where('m.film_festival_id=:id')
                ->andWhere('v.user_id=:userId')
                ->andWhere('v.score !=:score')
                ->setParameter('id', $filmFestivalId)
                ->setParameter('userId', $userId)
                ->setParameter('score', "-1")
                ->orderBy('m.id', 'DESC')
                ->fetchAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }
        if (empty($data)) {
            throw new DataNotFound("No movies voted");
        }
        return $this->fromDataToVote($data);
    }

    /**
     * @param mixed $dataOfVote
     * @return Vote
     */
    public function fromDataToVote(mixed $dataOfVote): Vote
    {
        return new Vote(
            new Id($dataOfVote['user_id']),
            Score::from((int)$dataOfVote['score']),
            new Id($dataOfVote['movie_id']),
            new Id($dataOfVote['group_id']),
        );
    }
}