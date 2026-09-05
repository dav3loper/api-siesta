<?php

namespace Siesta\Rating\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Rating\Domain\MovieRating;
use Siesta\Rating\Domain\MovieRatingCollection;
use Siesta\Rating\Domain\Rating;
use Siesta\Rating\Domain\RatingRepository;
use Siesta\Rating\Domain\RatingSummary;
use Siesta\Shared\Date\Date;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;
use Throwable;

class DoctrineRatingRepository implements RatingRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function upsert(Rating $rating): void
    {
        $existingRating = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('rating')
            ->where('user_id=:userId')
            ->andWhere('movie_id=:movieId')
            ->setParameter('userId', $rating->userId)
            ->setParameter('movieId', $rating->movieId)
            ->fetchOne();

        if ($existingRating) {
            $this->connection->update('rating', [
                'score' => $rating->score->value(),
                'updated_at' => new Date('now')
            ], [
                'id' => $existingRating
            ]);
            return;
        }

        $this->connection->insert('rating', [
            'user_id' => $rating->userId,
            'movie_id' => $rating->movieId,
            'score' => $rating->score->value(),
            'created_at' => new Date('now'),
            'updated_at' => new Date('now')
        ]);
    }

    /**
     * One query for the whole edition: the ratings of every movie are aggregated in the join
     * instead of asking for them movie by movie.
     *
     * @throws InternalError
     */
    public function findAllByFilmFestivalId(Id $filmFestivalId, Id $userId): MovieRatingCollection
    {
        try {
            $dataList = $this->connection->createQueryBuilder()
                ->select(
                    'm.id',
                    'm.title',
                    'm.poster',
                    'm.section',
                    'm.film_festival_id',
                    'AVG(r.score) AS average_score',
                    'COUNT(r.id) AS ratings_count',
                    'MAX(CASE WHEN r.user_id=:userId THEN r.score END) AS user_score'
                )
                ->from('movie', 'm')
                ->leftJoin('m', 'rating', 'r', 'r.movie_id=m.id')
                ->where('m.film_festival_id=:filmFestivalId')
                // Every selected column is grouped on purpose: MariaDB and MySQL do not agree
                // on what a grouped query may select.
                ->groupBy('m.id', 'm.title', 'm.poster', 'm.section', 'm.film_festival_id')
                ->orderBy('m.title', 'ASC')
                ->setParameter('filmFestivalId', $filmFestivalId)
                ->setParameter('userId', $userId)
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        return new MovieRatingCollection(array_map(
            fn (array $data): MovieRating => $this->fromDataToMovieRating($data),
            $dataList
        ));
    }

    /**
     * @throws InternalError
     */
    public function summaryOf(Id $movieId, Id $userId): RatingSummary
    {
        try {
            $data = $this->connection->createQueryBuilder()
                ->select(
                    'AVG(score) AS average_score',
                    'COUNT(id) AS ratings_count',
                    'MAX(CASE WHEN user_id=:userId THEN score END) AS user_score'
                )
                ->from('rating')
                ->where('movie_id=:movieId')
                ->setParameter('movieId', $movieId)
                ->setParameter('userId', $userId)
                ->fetchAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        return $data === false ? RatingSummary::withoutRatings() : $this->fromDataToSummary($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function fromDataToMovieRating(array $data): MovieRating
    {
        return new MovieRating(
            new Id((string)$data['id']),
            $data['title'],
            $data['poster'] ?? '',
            $data['section'],
            (int)$data['film_festival_id'],
            $this->fromDataToSummary($data)
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function fromDataToSummary(array $data): RatingSummary
    {
        return new RatingSummary(
            $data['average_score'] !== null ? (float)$data['average_score'] : null,
            (int)$data['ratings_count'],
            $data['user_score'] !== null ? (int)$data['user_score'] : null
        );
    }
}
