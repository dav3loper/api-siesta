<?php

namespace Siesta\Agent\Domain;

use Siesta\Shared\Exception\InternalError;

interface MovieCatalog
{
    /**
     * Movies of the current festival edition whose title matches the term.
     *
     * @throws InternalError
     */
    public function searchByTitle(string $term): CatalogMovieCollection;

    /**
     * Movies of the current festival edition programmed in the given section.
     *
     * @throws InternalError
     */
    public function searchBySection(string $section): CatalogMovieCollection;

    /**
     * Of the given titles, the ones that exist in the current festival edition.
     *
     * @param string[] $titles
     *
     * @return string[]
     *
     * @throws InternalError
     */
    public function existingTitles(array $titles): array;
}
