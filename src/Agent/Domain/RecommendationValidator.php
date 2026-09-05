<?php

namespace Siesta\Agent\Domain;

use Siesta\Shared\Exception\InternalError;

/**
 * The agent is told to wrap every movie title it cites between « », so the titles it
 * claims can be checked against the real catalog once the answer is complete.
 */
class RecommendationValidator
{
    private const QUOTED_TITLE_PATTERN = '/«([^»]{1,200})»/u';

    public function __construct(private readonly MovieCatalog $movieCatalog)
    {
    }

    /**
     * @return string[] titles the agent cited that are not in the catalog
     *
     * @throws InternalError
     */
    public function unknownTitles(string $answer): array
    {
        preg_match_all(self::QUOTED_TITLE_PATTERN, $answer, $matches);

        $citedTitles = array_values(array_unique(array_map('trim', $matches[1])));
        if ($citedTitles === []) {
            return [];
        }

        $knownTitles = array_map('mb_strtolower', $this->movieCatalog->existingTitles($citedTitles));

        return array_values(array_filter(
            $citedTitles,
            fn (string $title): bool => !in_array(mb_strtolower($title), $knownTitles, true)
        ));
    }
}
