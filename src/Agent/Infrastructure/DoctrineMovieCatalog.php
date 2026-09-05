<?php

namespace Siesta\Agent\Infrastructure;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Siesta\Agent\Domain\CatalogMovie;
use Siesta\Agent\Domain\CatalogMovieCollection;
use Siesta\Agent\Domain\MovieCatalog;
use Siesta\Shared\Exception\InternalError;
use Throwable;

class DoctrineMovieCatalog implements MovieCatalog
{
    private const MAX_RESULTS = 10;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws InternalError
     */
    public function searchByTitle(string $term): CatalogMovieCollection
    {
        try {
            $dataList = $this->connection->createQueryBuilder()
                ->select('title, section, duration, summary')
                ->from('movie')
                ->where('film_festival_id=:filmFestivalId')
                ->andWhere('title LIKE :term')
                ->setParameter('filmFestivalId', $this->currentFilmFestivalId())
                ->setParameter('term', '%' . $term . '%')
                ->setMaxResults(self::MAX_RESULTS)
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        return $this->fromDataToCatalogMovies($dataList);
    }

    /**
     * @throws InternalError
     */
    public function searchBySection(string $section): CatalogMovieCollection
    {
        try {
            $dataList = $this->connection->createQueryBuilder()
                ->select('title, section, duration, summary')
                ->from('movie')
                ->where('film_festival_id=:filmFestivalId')
                ->andWhere('section LIKE :section')
                ->setParameter('filmFestivalId', $this->currentFilmFestivalId())
                ->setParameter('section', '%' . $section . '%')
                ->setMaxResults(self::MAX_RESULTS)
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        return $this->fromDataToCatalogMovies($dataList);
    }

    /**
     * @param string[] $titles
     *
     * @return string[]
     *
     * @throws InternalError
     */
    public function existingTitles(array $titles): array
    {
        if ($titles === []) {
            return [];
        }

        try {
            return $this->connection->createQueryBuilder()
                ->select('title')
                ->from('movie')
                ->where('film_festival_id=:filmFestivalId')
                ->andWhere('title IN (:titles)')
                ->setParameter('filmFestivalId', $this->currentFilmFestivalId())
                ->setParameter('titles', $titles, ArrayParameterType::STRING)
                ->fetchFirstColumn();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }
    }

    /**
     * The agent always talks about the edition being voted on, which is the latest one.
     *
     * @throws InternalError
     */
    private function currentFilmFestivalId(): string
    {
        try {
            $id = $this->connection->createQueryBuilder()
                ->select('id')
                ->from('film_festival')
                ->orderBy('edition_number', 'DESC')
                ->setMaxResults(1)
                ->fetchOne();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        if ($id === false) {
            throw new InternalError('There is no film festival edition to search movies in');
        }

        return (string)$id;
    }

    /**
     * @param array<int, array<string, mixed>> $dataList
     */
    private function fromDataToCatalogMovies(array $dataList): CatalogMovieCollection
    {
        return new CatalogMovieCollection(array_map(
            fn (array $data): CatalogMovie => new CatalogMovie(
                $data['title'],
                $data['section'],
                $data['duration'] !== null ? (int)$data['duration'] : null,
                $data['summary'],
            ),
            $dataList
        ));
    }
}
