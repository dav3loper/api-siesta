<?php

namespace Siesta\Agent\Infrastructure;

use Anthropic\Client;
use Siesta\Agent\Domain\AgentClient;

class AnthropicAgentClient implements AgentClient
{
    public function __construct(private readonly string $apiKey, private readonly string $model)
    {
    }

    public function streamAnswer(string $systemPrompt, string $userMessage): iterable
    {
        $client = new Client(apiKey: $this->apiKey);

        $stream = $client->messages->createStream(
            maxTokens: 1024,
            messages: [['role' => 'user', 'content' => $userMessage]],
            model: $this->model,
            system: $systemPrompt,
        );

        foreach ($stream as $event) {
            if ($event->type === 'content_block_delta' && $event->delta->type === 'text_delta') {
                yield $event->delta->text;
            }
        }
    }
}
