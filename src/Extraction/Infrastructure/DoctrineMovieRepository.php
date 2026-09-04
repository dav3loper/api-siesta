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
    const NO_TRAILER = 'notrailer';

    public function __construct(private Connection $connection)
    {
    }

    public function store(Movie $movie): void
    {
        $existing = $this->connection->createQueryBuilder()
            ->select('id', 'poster', 'trailer_id', 'summary')
            ->from(self::TABLE)
            ->where('title = :title')
            ->setParameter('title', $movie->title)
            ->fetchAssociative();
        if ($existing) {
            $this->connection->update(self::TABLE, [
                'poster' => $movie->poster !== '' ? $movie->poster : $existing['poster'],
                'trailer_id' => $movie->trailer_id !== self::NO_TRAILER ? $movie->trailer_id : $existing['trailer_id'],
                'duration' => $movie->duration,
                'summary' => $movie->summary !== '' ? $movie->summary : $existing['summary'],
                'link' => $movie->link,
                'section' => $movie->section,
                'updated_at' => new Date('now')
            ], ['id' => $existing['id']]);
            if ($movie->sessions) {
                $this->replaceSessions((int)$existing['id'], $movie->sessions);
            }
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
        $movieId = (int)$this->connection->lastInsertId();
        $this->replaceSessions($movieId, $movie->sessions);
    }

    private function replaceSessions(int $movieId, array $sessions): void
    {
        $this->connection->delete(self::SESSIONS_TABLE, ['movie_id' => $movieId]);
        array_map(fn(array $session) =>
            $this->connection->insert(self::SESSIONS_TABLE, [
                'movie_id' => $movieId,
                'location' => $session['location'],
                'init_date' => $session['init_date'],
                'end_date' => $session['end_date'],
                'movies' => implode(',', $session['films'])
            ]), $sessions);
    }
}