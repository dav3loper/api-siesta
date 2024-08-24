<?php

namespace Siesta\Movie\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Movie\Domain\Movie;
use Siesta\Movie\Domain\MovieRepository;
use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;
use Throwable;

class DoctrineMovieRepository implements MovieRepository
{

    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws DataNotFound
     * @throws InternalError
     */
    public function getById(string $id): Movie
    {
        try {
            $data = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('movie')
                ->where('id=:id')
                ->setParameter('id', $id)
                ->fetchAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }
        if (empty($data)) {
            throw new DataNotFound("Movie with $id not found");
        }
        return $this->fromDataToMovie($data);
    }

    private function fromDataToMovie(array $data): Movie
    {
        return new Movie(
            new Id($data['id']),
            $data['title'],
            $data['poster'],
            $data['trailer_id'],
            (int)$data['duration'],
            $data['summary'],
            $data['link'],
            $data['comments'],
            (int)$data['film_festival_id'],
            $data['alias']
        );

    }

    /**
     * @throws DataNotFound
     * @throws InternalError
     */
    private function getVotesForMovie(string $id): array
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
        if (empty($data)) {
            return [];
        }
        return $this->fromDataToVoteList($data);
    }

    private function fromDataToVoteList(array $data)
    {

    }
}