<?php

namespace Siesta\Extraction\Infrastructure;

use Siesta\Extraction\Domain\SynopsisFinder;
use Siesta\Extraction\Domain\TranslatorService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class TmdbSynopsisFinder implements SynopsisFinder
{
    private const SEARCH_URL = 'https://api.themoviedb.org/3/search/movie';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string              $readAccessToken,
        private readonly TranslatorService    $translator,
    )
    {
    }

    public function findByTitle(string $title, ?int $year): string
    {
        try {
            $overview = $this->search($title, $year, 'es-ES');
            if ($overview !== '') {
                return $overview;
            }

            $originalOverview = $this->search($title, $year, 'en-US');

            return $originalOverview !== '' ? $this->translator->translateToSpanish($originalOverview) : '';
        } catch (Throwable) {
            return '';
        }
    }

    private function search(string $title, ?int $year, string $language): string
    {
        $query = ['query' => $title, 'language' => $language];
        if ($year) {
            $query['year'] = $year;
        }

        $results = $this->httpClient->request('GET', self::SEARCH_URL, [
            'query' => $query,
            'headers' => ['Authorization' => "Bearer $this->readAccessToken"],
        ])->toArray()['results'] ?? [];

        return $results[0]['overview'] ?? '';
    }
}
