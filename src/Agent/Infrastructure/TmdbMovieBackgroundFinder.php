<?php

namespace Siesta\Agent\Infrastructure;

use Siesta\Agent\Domain\Background\MovieBackground;
use Siesta\Agent\Domain\Background\MovieBackgroundFinder;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * A miss costs three calls to TMDB (search, detail with credits, filmography of the director),
 * which is why the answer is cached before being handed to the agent.
 */
class TmdbMovieBackgroundFinder implements MovieBackgroundFinder
{
    private const SEARCH_URL = 'https://api.themoviedb.org/3/search/movie';
    private const MOVIE_URL = 'https://api.themoviedb.org/3/movie/';
    private const PERSON_URL = 'https://api.themoviedb.org/3/person/';
    private const LANGUAGE = 'es-ES';
    private const MAX_CAST = 4;
    private const MAX_DIRECTOR_MOVIES = 5;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $readAccessToken,
    )
    {
    }

    public function findByTitle(string $title, ?int $year): ?MovieBackground
    {
        $found = $this->search($title, $year);
        if ($found === null) {
            return null;
        }

        $movie = $this->get(self::MOVIE_URL . $found['id'], ['append_to_response' => 'credits']);
        $directors = $this->directorsOf($movie['credits']['crew'] ?? []);

        return new MovieBackground(
            // The catalog of the festival uses original titles, not the Spanish ones.
            $movie['original_title'] ?? $movie['title'] ?? $title,
            $this->yearOf($movie['release_date'] ?? null),
            $directors === [] ? null : implode(', ', array_column($directors, 'name')),
            array_column($movie['genres'] ?? [], 'name'),
            $this->mainCast($movie['credits']['cast'] ?? []),
            $directors === [] ? [] : $this->otherMoviesDirectedBy((int)$directors[0]['id'], (int)$movie['id']),
            $this->audienceScoreOf($movie),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function search(string $title, ?int $year): ?array
    {
        $query = ['query' => $title];
        if ($year !== null) {
            $query['year'] = $year;
        }

        $results = $this->get(self::SEARCH_URL, $query)['results'] ?? [];

        return $results[0] ?? null;
    }

    /**
     * Co-directed movies are common enough in the festival to not credit only the first name.
     *
     * @param array<int, array<string, mixed>> $crew
     *
     * @return array<int, array<string, mixed>>
     */
    private function directorsOf(array $crew): array
    {
        return array_values(array_filter(
            $crew,
            fn (array $member): bool => ($member['job'] ?? '') === 'Director'
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $cast
     *
     * @return string[]
     */
    private function mainCast(array $cast): array
    {
        return array_column(array_slice($cast, 0, self::MAX_CAST), 'name');
    }

    /**
     * The most popular ones first: what the user is most likely to have seen is the useful
     * reference, not the whole filmography. Credits with no release date are announced
     * projects with nothing to say about them yet, so they are left out.
     *
     * @return string[]
     */
    private function otherMoviesDirectedBy(int $directorId, int $currentMovieId): array
    {
        $credits = $this->get(self::PERSON_URL . $directorId . '/movie_credits')['crew'] ?? [];

        $directed = array_values(array_filter(
            $credits,
            fn (array $credit): bool => ($credit['job'] ?? '') === 'Director'
                && (int)($credit['id'] ?? 0) !== $currentMovieId
                && $this->yearOf($credit['release_date'] ?? null) !== null
        ));

        usort($directed, fn (array $a, array $b): int => ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0));

        return array_map(
            fn (array $credit): string => $credit['title'] . ' (' . $this->yearOf($credit['release_date']) . ')',
            array_slice($directed, 0, self::MAX_DIRECTOR_MOVIES)
        );
    }

    /**
     * @param array<string, mixed> $movie
     */
    private function audienceScoreOf(array $movie): ?float
    {
        $score = (float)($movie['vote_average'] ?? 0);

        return $score > 0 ? round($score, 1) : null;
    }

    private function yearOf(?string $releaseDate): ?int
    {
        if ($releaseDate === null || $releaseDate === '') {
            return null;
        }

        return (int)substr($releaseDate, 0, 4);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function get(string $url, array $query = []): array
    {
        return $this->httpClient->request('GET', $url, [
            'query' => array_merge(['language' => self::LANGUAGE], $query),
            'headers' => ['Authorization' => "Bearer $this->readAccessToken"],
        ])->toArray();
    }
}
