<?php

namespace Siesta\Extraction\Infrastructure;

use Exception;
use Google\Service\YouTube;
use Google\Service\YouTube\SearchResult;
use Siesta\Extraction\Domain\FinderVideoService;

class YoutubeFinderVideoService implements FinderVideoService
{

    private const PART_TO_FIND = 'snippet';
    private const QUERY_PARAM = 'q';
    private const TYPE = 'type';
    private const ALLOWED_TYPE = 'video';

    public function __construct(private readonly YouTube $client)
    {
    }

    public function findByText(string $text): string
    {
        try {
            $firstVideo = $this->getFirstVideoByText($text);

            return $firstVideo->getId()->getVideoId();
        } catch (Exception $e) {
            echo $e->getMessage();
            return 'notrailer';
        }
    }

    private function getFirstVideoByText(string $text): SearchResult
    {
        $videos = $this->client->search->listSearch(self::PART_TO_FIND, [self::QUERY_PARAM => $text, self::TYPE => self::ALLOWED_TYPE]);

        if (!$videos->getItems()) {
            throw new \Exception('No video found');
        }

        return current($videos->getItems());
    }
}