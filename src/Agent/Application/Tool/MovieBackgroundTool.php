<?php

namespace Siesta\Agent\Application\Tool;

use Siesta\Agent\Domain\Background\MovieBackground;
use Siesta\Agent\Domain\Background\MovieBackgroundFinder;
use Siesta\Agent\Domain\Background\MovieBackgroundRepository;
use Siesta\Agent\Domain\Tool\AgentTool;

class MovieBackgroundTool implements AgentTool
{
    public function __construct(
        private readonly MovieBackgroundRepository $movieBackgroundRepository,
        private readonly MovieBackgroundFinder $movieBackgroundFinder,
    )
    {
    }

    public function name(): string
    {
        return 'movie_background';
    }

    public function description(): string
    {
        return 'Da información de una película más allá de su sinopsis: quién la dirige, qué otras '
            . 'películas ha dirigido, reparto principal, géneros, premios y nominaciones que ha '
            . 'recibido, y valoración media del público. Úsala cuando el usuario pregunte por el '
            . 'director, el reparto, los premios o a qué se parece una película. '
            . 'Los datos vienen de una base externa, así que puede no haber nada de un estreno muy reciente: '
            . 'en ese caso dilo, no lo supongas.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Título completo de la película, tal y como aparece en el catálogo',
                ],
                'year' => [
                    'type' => 'integer',
                    'description' => 'Año de la película, si se conoce, para desambiguar títulos repetidos',
                ],
            ],
            'required' => ['title'],
        ];
    }

    public function execute(array $input): string
    {
        $title = trim((string)($input['title'] ?? ''));
        if ($title === '') {
            return 'Indica el título de la película sobre la que quieres información.';
        }

        $year = isset($input['year']) ? (int)$input['year'] : null;

        $background = $this->movieBackgroundRepository->findBySearchedTitle($title);
        if ($background === null) {
            $background = $this->movieBackgroundFinder->findByTitle($title, $year);

            if ($background === null) {
                return "No hay información externa sobre \"{$title}\".";
            }

            $this->movieBackgroundRepository->save($title, $background);
        }

        return $this->format($background);
    }

    private function format(MovieBackground $background): string
    {
        $lines = [$background->title . ($background->year !== null ? " ({$background->year})" : '')];

        if ($background->director !== null) {
            $lines[] = "Dirigida por: {$background->director}";
        }
        if ($background->otherMoviesByDirector !== []) {
            $lines[] = 'Otras películas del director: ' . implode(', ', $background->otherMoviesByDirector);
        }
        if ($background->mainCast !== []) {
            $lines[] = 'Reparto principal: ' . implode(', ', $background->mainCast);
        }
        if ($background->genres !== []) {
            $lines[] = 'Géneros: ' . implode(', ', $background->genres);
        }
        if ($background->awards !== []) {
            $lines[] = 'Premios y nominaciones: ' . implode('; ', $background->awards);
        }
        if ($background->audienceScore !== null) {
            // The number of votes goes with the score on purpose: an 8 out of four votes on a
            // premiere nobody has seen yet is not the same thing as an 8 out of five thousand.
            $lines[] = "Valoración media del público: {$background->audienceScore}/10"
                . " ({$background->audienceVotes} votos)";
        }

        return implode("\n", $lines);
    }
}
