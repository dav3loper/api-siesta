<?php

namespace Siesta\Agent\Application\Tool;

use Siesta\Agent\Domain\CatalogMovie;
use Siesta\Agent\Domain\CatalogMovieCollection;
use Siesta\Agent\Domain\MovieCatalog;
use Siesta\Agent\Domain\Tool\AgentTool;

class SearchCatalogTool implements AgentTool
{
    public function __construct(private readonly MovieCatalog $movieCatalog)
    {
    }

    public function name(): string
    {
        return 'search_catalog';
    }

    public function description(): string
    {
        return 'Busca películas en el catálogo oficial de la edición actual del Festival de Sitges. '
            . 'Úsala antes de recomendar una película o de afirmar que está programada: '
            . 'solo puedes hablar de películas que devuelva esta herramienta.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Parte del título a buscar',
                ],
                'section' => [
                    'type' => 'string',
                    'description' => 'Sección del festival en la que buscar, por ejemplo "Oficial Fantàstic Competició"',
                ],
            ],
        ];
    }

    public function execute(array $input): string
    {
        $title = $input['title'] ?? null;
        $section = $input['section'] ?? null;

        if ($title !== null && $title !== '') {
            return $this->format($this->movieCatalog->searchByTitle($title));
        }

        if ($section !== null && $section !== '') {
            return $this->format($this->movieCatalog->searchBySection($section));
        }

        return 'Indica un título o una sección para buscar.';
    }

    private function format(CatalogMovieCollection $movies): string
    {
        if ($movies->count() === 0) {
            return 'Ninguna película del catálogo de esta edición coincide con la búsqueda.';
        }

        $lines = [];
        foreach ($movies as $movie) {
            /** @var CatalogMovie $movie */
            $details = [];
            if ($movie->section !== null) {
                $details[] = "sección: {$movie->section}";
            }
            if ($movie->duration !== null) {
                $details[] = "duración: {$movie->duration} min";
            }

            $line = "- {$movie->title}";
            if ($details !== []) {
                $line .= ' (' . implode(', ', $details) . ')';
            }
            if ($movie->summary !== null && $movie->summary !== '') {
                $line .= "\n  {$movie->summary}";
            }

            $lines[] = $line;
        }

        return implode("\n", $lines);
    }
}
