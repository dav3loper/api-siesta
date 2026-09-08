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
            ->select('id', 'poster', 'trailer_id', 'summary', 'poster_locked', 'trailer_locked')
            ->from(self::TABLE)
            ->where('title = :title')
            ->setParameter('title', $movie->title)
            ->fetchAssociative();
        if ($existing) {
            $this->connection->update(self::TABLE, [
                'poster' => $this->keepsExistingPoster($movie, $existing) ? $existing['poster'] : $movie->poster,
                'trailer_id' => $this->keepsExistingTrailer($movie, $existing) ? $existing['trailer_id'] : $movie->trailer_id,
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

    /**
     * A poster corrected by a person wins over whatever the import finds, the same way an empty
     * poster in the import never erases the one already stored.
     *
     * @param array<string, mixed> $existing
     */
    private function keepsExistingPoster(Movie $movie, array $existing): bool
    {
        return (bool)$existing['poster_locked'] || $movie->poster === '';
    }

    /**
     * @param array<string, mixed> $existing
     */
    private function keepsExistingTrailer(Movie $movie, array $existing): bool
    {
        return (bool)$existing['trailer_locked'] || $movie->trailer_id === self::NO_TRAILER;
    }

    public function alreadyHasTrailer(string $title): bool
    {
        $trailerId = $this->connection->createQueryBuilder()
            ->select('trailer_id')
            ->from(self::TABLE)
            ->where('title = :title')
            ->setParameter('title', $title)
            ->fetchOne();

        return $trailerId !== false && $trailerId !== self::NO_TRAILER;
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