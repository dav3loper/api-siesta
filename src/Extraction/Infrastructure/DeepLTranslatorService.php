<?php

namespace Siesta\Extraction\Infrastructure;

use Siesta\Extraction\Domain\TranslatorService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class DeepLTranslatorService implements TranslatorService
{
    private const TRANSLATE_URL = 'https://api-free.deepl.com/v2/translate';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string              $apiKey,
    )
    {
    }

    public function translateToSpanish(string $text): string
    {
        try {
            $translations = $this->httpClient->request('POST', self::TRANSLATE_URL, [
                'headers' => ['Authorization' => "DeepL-Auth-Key $this->apiKey"],
                'body' => ['text' => $text, 'target_lang' => 'ES'],
            ])->toArray()['translations'] ?? [];

            return $translations[0]['text'] ?? $text;
        } catch (Throwable) {
            return $text;
        }
    }
}
