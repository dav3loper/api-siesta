<?php

namespace Siesta\Rating\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Rating\Domain\Rating;
use Siesta\Rating\Domain\RatingRepository;
use Siesta\Shared\Date\Date;

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
}
