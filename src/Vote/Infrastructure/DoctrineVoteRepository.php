<?php

namespace Siesta\Vote\Infrastructure;

use Doctrine\DBAL\Connection;
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
    public function getAllByMovieId(Id $id): VoteCollection
    {
        try {
            $data = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('vote')
                ->where('movie_id=:id')
                ->setParameter('id', $id)
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }
        return $this->fromDataToVoteList($data);
    }

    private function fromDataToVoteList(array $data): VoteCollection
    {
        $voteCollection = new VoteCollection([]);
        foreach ($data as $dataOfVote) {
            $voteCollection->add(new Vote(
                new Id($dataOfVote['id']),
                Score::from((int)$dataOfVote['score']),
                new Id($dataOfVote['movie_id']),
            ));

        }
        return $voteCollection;
    }
}