<?php

namespace Siesta\Agent\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Agent\Domain\RatedMovie;
use Siesta\Agent\Domain\RatedMovieCollection;
use Siesta\Agent\Domain\UserProfile;
use Siesta\Agent\Domain\UserProfileRepository;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;
use Siesta\Shared\Score\Score;
use Throwable;

class DoctrineUserProfileRepository implements UserProfileRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws InternalError
     */
    public function getByUserId(Id $userId): UserProfile
    {
        try {
            $data = $this->connection->createQueryBuilder()
                ->select('m.title AS title, v.score AS vote_score, r.score AS rating_score')
                ->from('movie', 'm')
                ->leftJoin('m', 'vote', 'v', 'v.movie_id = m.id AND v.user_id = :userId AND v.score != :notYet')
                ->leftJoin('m', 'rating', 'r', 'r.movie_id = m.id AND r.user_id = :userId')
                ->where('v.id IS NOT NULL OR r.id IS NOT NULL')
                ->setParameter('userId', $userId)
                ->setParameter('notYet', Score::NOT_YET->value)
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        $ratedMovies = new RatedMovieCollection([]);
        foreach ($data as $row) {
            $ratedMovies->add(new RatedMovie(
                $row['title'],
                $row['vote_score'] !== null ? Score::from((int)$row['vote_score']) : null,
                $row['rating_score'] !== null ? (int)$row['rating_score'] : null,
            ));
        }

        return new UserProfile($ratedMovies);
    }
}
