<?php

namespace Siesta\Extraction\Infrastructure;

use DOMDocument;
use DOMXPath;
use Siesta\Extraction\Domain\MovieListEntry;
use Siesta\Extraction\Domain\MovieListFinder;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LetterboxdMovieListFinder implements MovieListFinder
{
    private const HOST = 'https://letterboxd.com';
    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';
    private const MAX_PAGES = 50;

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function findAll(string $url): array
    {
        $baseUrl = rtrim($url, '/') . '/';

        $entries = [];
        for ($page = 1; $page <= self::MAX_PAGES; $page++) {
            $pageUrl = $page === 1 ? $baseUrl : $baseUrl . "page/$page/";
            $pageEntries = $this->findAllOnPage($pageUrl);
            if (!$pageEntries) {
                break;
            }
            $entries = [...$entries, ...$pageEntries];
        }

        return $entries;
    }

    /**
     * @return MovieListEntry[]
     */
    private function findAllOnPage(string $pageUrl): array
    {
        $html = $this->httpClient->request('GET', $pageUrl, [
            'headers' => ['User-Agent' => self::USER_AGENT],
        ])->getContent();

        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $entries = [];
        foreach ($xpath->query('//div[@data-component-class="LazyPoster"]') as $node) {
            $name = $node->getAttribute('data-item-name');
            $link = $node->getAttribute('data-item-link') ?: $node->getAttribute('data-target-link');
            if (!$name || !$link) {
                continue;
            }
            $entries[] = $this->toMovieListEntry($name, $link);
        }

        return $entries;
    }

    private function toMovieListEntry(string $name, string $link): MovieListEntry
    {
        $title = $name;
        $year = null;
        if (preg_match('/^(.*)\s\((\d{4})\)$/', $name, $matches)) {
            $title = $matches[1];
            $year = (int)$matches[2];
        }

        return new MovieListEntry($title, $year, self::HOST . $link);
    }
}
