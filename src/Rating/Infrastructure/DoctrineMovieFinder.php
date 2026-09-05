<?php

namespace Siesta\Rating\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Rating\Domain\MovieFinder;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;
use Throwable;

class DoctrineMovieFinder implements MovieFinder
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws InternalError
     */
    public function exists(Id $movieId): bool
    {
        try {
            $id = $this->connection->createQueryBuilder()
                ->select('id')
                ->from('movie')
                ->where('id=:id')
                ->setParameter('id', $movieId)
                ->fetchOne();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        return $id !== false;
    }
}
