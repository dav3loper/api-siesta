<?php

namespace Siesta\Extraction\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Extraction\Domain\Movie;
use Siesta\Extraction\Domain\MovieRepository;
use Siesta\Shared\Date\Date;

class DoctrineMovieRepository implements MovieRepository
{
    const TABLE = 'movie';
    const SESSIONS_TABLE = 'sessions';

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
                'section' => $movie->section,
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
            'section' => $movie->section,
            'created_at' => new Date('now'),
            'updated_at' => new Date('now')
        ]);
        $movieId = $this->connection->lastInsertId();
        array_map(fn(array $session) =>
            $this->connection->insert(self::SESSIONS_TABLE, [
                'movie_id' => $movieId,
                'location' => $session['location'],
                'init_date' => $session['init_date'],
                'end_date' => $session['end_date'],
                'movies' => implode(',', $session['films'])
            ]), $movie->sessions);
    }
}