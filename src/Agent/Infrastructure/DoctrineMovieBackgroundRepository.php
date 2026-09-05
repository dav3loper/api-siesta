<?php

namespace Siesta\Agent\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Agent\Domain\Background\MovieBackground;
use Siesta\Agent\Domain\Background\MovieBackgroundRepository;
use Siesta\Shared\Date\Date;
use Siesta\Shared\Exception\InternalError;
use Throwable;

class DoctrineMovieBackgroundRepository implements MovieBackgroundRepository
{
    private const TABLE = 'movie_background';

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws InternalError
     */
    public function findBySearchedTitle(string $searchedTitle): ?MovieBackground
    {
        try {
            $row = $this->connection->createQueryBuilder()
                ->select('title', 'year', 'director', 'genres', 'main_cast', 'other_movies_by_director', 'audience_score')
                ->from(self::TABLE)
                ->where('searched_title=:searchedTitle')
                ->setParameter('searchedTitle', $searchedTitle)
                ->setMaxResults(1)
                ->fetchAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        return $row === false ? null : $this->fromDataToMovieBackground($row);
    }

    /**
     * @throws InternalError
     */
    public function save(string $searchedTitle, MovieBackground $background): void
    {
        try {
            $this->connection->insert(self::TABLE, [
                'searched_title' => $searchedTitle,
                'title' => $background->title,
                'year' => $background->year,
                'director' => $background->director,
                'genres' => json_encode($background->genres, JSON_UNESCAPED_UNICODE),
                'main_cast' => json_encode($background->mainCast, JSON_UNESCAPED_UNICODE),
                'other_movies_by_director' => json_encode($background->otherMoviesByDirector, JSON_UNESCAPED_UNICODE),
                'audience_score' => $background->audienceScore,
                'created_at' => new Date('now'),
                'updated_at' => new Date('now'),
            ]);
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function fromDataToMovieBackground(array $row): MovieBackground
    {
        return new MovieBackground(
            $row['title'],
            $row['year'] !== null ? (int)$row['year'] : null,
            $row['director'],
            json_decode($row['genres'], true),
            json_decode($row['main_cast'], true),
            json_decode($row['other_movies_by_director'], true),
            $row['audience_score'] !== null ? (float)$row['audience_score'] : null,
        );
    }
}
