<?php

namespace Siesta\Extraction\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Extraction\Domain\Movie;
use Siesta\Extraction\Domain\MovieRepository;
use Siesta\Shared\Date\Date;

class DoctrineMovieRepository implements MovieRepository
{
    const TABLE = 'movie';

    public function __construct(private Connection $connection)
    {
    }

    public function store(Movie $movie): void
    {
        $wasAlreadyStored = $this->connection->createQueryBuilder()
            ->select('id')
            ->from(self::TABLE)
            ->where('title = :title')
            ->setParameter('title', $movie->title)
            ->fetchOne();
        if($wasAlreadyStored){
            $this->connection->update(self::TABLE, [
                'poster' => $movie->poster,
                'trailer_id' => $movie->trailer_id,
                'duration' => $movie->duration,
                'summary' => $movie->summary,
                'link' => $movie->link,
                'updated_at' => new Date('now')
            ], ['id' => $wasAlreadyStored]);
            return;
        }

        $this->connection->insert(self::TABLE, [
            'title' => $movie->title,
            'poster' => $movie->poster,
            'trailer_id' => $movie->trailer_id,
            'duration' => $movie->duration,
            'summary' => $movie->summary,
            'link' => $movie->link,
            'film_festival_id' => $movie->film_festival_id,
            'created_at' => new Date('now'),
            'updated_at' => new Date('now')
        ]);
    }
}