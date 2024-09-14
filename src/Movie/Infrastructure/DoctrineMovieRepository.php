<?php

namespace Siesta\Movie\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Movie\Domain\Movie;
use Siesta\Movie\Domain\MovieRepository;
use Siesta\Movie\Domain\Session;
use Siesta\Shared\Date\Date;
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
        $sessionsData = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('sessions')
            ->where('movie_id=:movie_id')
            ->setParameter('movie_id', $data['id'])
            ->fetchAllAssociative();
        return $this->fromDataToMovie($data, $sessionsData);
    }

    private function fromDataToMovie(array $data, array $sessionsData): Movie
    {
        $sessionList = array_map(fn($data) =>
        new Session($data['location'], new Date($data['init_date']), new Date($data['end_date']), $data['movies'])
            , $sessionsData);
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
            $data['alias'],
            $data['section'],
            $sessionList
        );

    }

    /**
     * @throws DataNotFound
     * @throws InternalError
     */
    public function getNextMovie(Id $movieId, Id $filmFestivalId): Movie
    {
        try {
            $data = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('movie')
                ->where('id>:id')
                ->andWhere('film_festival_id=:film_festival_id')
                ->setParameter('id', $movieId)
                ->setParameter('film_festival_id', $filmFestivalId)
                ->orderBy("id ASC")
                ->fetchAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }
        if (empty($data)) {
            throw new DataNotFound("No more movies left");
        }
        return $this->fromDataToMovie($data, []);
    }

    /**
     * @return Movie[]
     * @throws InternalError
     */
    public function getAllByFilmFestivalId(int $filmFestivalId): array
    {
        try {
            $dataList = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('movie')
                ->where('film_festival_id=:id')
                ->setParameter('id', $filmFestivalId)
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }


        return array_map(fn($data) => $this->fromDataToMovie($data, []), $dataList);
    }
}