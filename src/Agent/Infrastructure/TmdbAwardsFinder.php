<?php

namespace Siesta\Agent\Infrastructure;

use DOMDocument;
use DOMNode;
use DOMXPath;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * TMDB only publishes the awards of a movie on its website, not through the API, so they
 * are read from the HTML. Kept apart from the rest of the TMDB mapping because this is the
 * part that breaks when they change their markup.
 *
 * Each entry looks like this, repeated once per nomination:
 *
 *     <p><a href="/award/1-academy-awards/ceremony/97">97th Premios Óscar (2025)</a></p>
 *     <p><span>Nominado</span> <a href="/award/1-academy-awards/category/1-best-picture">Mejor película</a></p>
 */
class TmdbAwardsFinder
{
    private const AWARDS_URL = 'https://www.themoviedb.org/movie/%d/awards';
    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36';
    private const WINNER = 'Ganador';
    private const MAX_AWARDS = 6;

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    /**
     * @return string[]
     */
    public function findByMovieId(int $movieId): array
    {
        $xpath = $this->documentOf(sprintf(self::AWARDS_URL, $movieId));

        $awards = [];
        foreach ($xpath->query('//a[contains(@href, "/ceremony/")]') as $ceremony) {
            $award = $this->awardOf($xpath, $ceremony);
            if ($award !== null) {
                $awards[] = $award;
            }
        }

        // What it won says more than what it was nominated for, so it survives the cut first.
        usort($awards, fn (array $a, array $b): int => $b['won'] <=> $a['won']);

        return array_column(array_slice($awards, 0, self::MAX_AWARDS), 'text');
    }

    /**
     * @return array{won: bool, text: string}|null
     */
    private function awardOf(DOMXPath $xpath, DOMNode $ceremony): ?array
    {
        $result = $xpath->query('./parent::p/following-sibling::p[1]', $ceremony)->item(0);
        if ($result === null) {
            return null;
        }

        $status = $this->textOf($xpath->query('./span', $result)->item(0));
        $category = $this->textOf($xpath->query('./a', $result)->item(0));
        if ($status === '' || $category === '') {
            return null;
        }

        return [
            'won' => $status === self::WINNER,
            'text' => "{$status}: {$category} ({$this->textOf($ceremony)})",
        ];
    }

    private function documentOf(string $url): DOMXPath
    {
        $html = $this->httpClient->request('GET', $url, [
            'headers' => ['User-Agent' => self::USER_AGENT, 'Accept-Language' => 'es-ES'],
        ])->getContent();

        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($dom);
    }

    private function textOf(?DOMNode $node): string
    {
        return $node === null ? '' : trim($node->textContent);
    }
}
