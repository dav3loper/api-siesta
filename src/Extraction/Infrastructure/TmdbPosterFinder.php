<?php

namespace Siesta\Extraction\Infrastructure;

use Siesta\Extraction\Domain\PosterFinder;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class TmdbPosterFinder implements PosterFinder
{
    private const SEARCH_URL = 'https://api.themoviedb.org/3/search/movie';
    private const IMAGE_URL = 'https://image.tmdb.org/t/p/w500';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string              $readAccessToken,
    )
    {
    }

    public function findByTitle(string $title, ?int $year): string
    {
        try {
            $query = ['query' => $title];
            if ($year) {
                $query['year'] = $year;
            }

            $results = $this->httpClient->request('GET', self::SEARCH_URL, [
                'query' => $query,
                'headers' => ['Authorization' => "Bearer $this->readAccessToken"],
            ])->toArray()['results'] ?? [];
            $posterPath = $results[0]['poster_path'] ?? null;

            return $posterPath ? self::IMAGE_URL . $posterPath : '';
        } catch (Throwable) {
            return '';
        }
    }
}
