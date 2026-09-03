<?php

namespace Siesta\FilmFestival\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\FilmFestival\Domain\FilmFestival;
use Siesta\FilmFestival\Domain\FilmFestivalRepository;
use Siesta\Shared\Date\Date;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;
use Throwable;

class DoctrineFilmFestivalRepository implements FilmFestivalRepository
{

    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws InternalError
     */
    public function getAll(): array
    {
        try {
            $dataList = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('film_festival')
                ->orderBy('start_date', 'DESC')
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        return array_map(fn(array $data) => new FilmFestival(
            new Id($data['id']),
            (int)$data['edition_number'],
            $data['name'],
            new Date($data['start_date']),
            new Date($data['end_date']),
        ), $dataList);
    }
}
