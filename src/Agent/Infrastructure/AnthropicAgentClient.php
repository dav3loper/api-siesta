<?php

namespace Siesta\Agent\Infrastructure;

use Anthropic\Client;
use Anthropic\Lib\Streaming\MessageAccumulator;
use Anthropic\Messages\RawContentBlockDeltaEvent;
use Anthropic\Messages\TextDelta;
use Anthropic\Messages\ToolUseBlock;
use Siesta\Agent\Domain\AgentClient;
use Siesta\Agent\Domain\ConversationTurn;
use Siesta\Agent\Domain\ConversationTurnCollection;
use Siesta\Agent\Domain\Stream\TextChunk;
use Siesta\Agent\Domain\Stream\ToolInvoked;
use Siesta\Agent\Domain\Tool\AgentTool;
use Siesta\Agent\Domain\Tool\AgentToolCollection;
use Siesta\Agent\Domain\UserMessage;
use Siesta\Shared\Exception\InternalError;
use Throwable;

class AnthropicAgentClient implements AgentClient
{
    private const MAX_TOKENS = 1024;
    private const MAX_TOOL_ITERATIONS = 5;

    private Client $client;

    public function __construct(string $apiKey, private readonly string $model)
    {
        $this->client = new Client(apiKey: $apiKey);
    }

    /**
     * @throws InternalError
     */
    public function streamAnswer(
        string $systemPrompt,
        ConversationTurnCollection $history,
        UserMessage $userMessage,
        AgentToolCollection $tools
    ): iterable {
        $messages = $this->messagesFrom($history, $userMessage);
        $toolDefinitions = $this->toolDefinitions($tools);

        for ($iteration = 0; $iteration < self::MAX_TOOL_ITERATIONS; $iteration++) {
            $accumulator = MessageAccumulator::forMessages();

            try {
                $stream = $this->client->messages->createStream(
                    maxTokens: self::MAX_TOKENS,
                    messages: $messages,
                    model: $this->model,
                    system: $systemPrompt,
                    tools: $toolDefinitions,
                );

                foreach ($stream as $event) {
                    $accumulator->accumulate($event);

                    if ($event instanceof RawContentBlockDeltaEvent && $event->delta instanceof TextDelta) {
                        yield new TextChunk($event->delta->text);
                    }
                }
            } catch (Throwable $e) {
                throw new InternalError($e->getMessage());
            }

            $message = $accumulator->message();
            if ($message->stopReason !== 'tool_use') {
                return;
            }

            $messages[] = ['role' => 'assistant', 'content' => $message->content];

            $toolResults = [];
            foreach ($message->content as $block) {
                if (!$block instanceof ToolUseBlock) {
                    continue;
                }

                $result = $this->executeTool($tools, $block->name, $block->input);
                yield new ToolInvoked($block->name, $block->input, $result);

                $toolResults[] = [
                    'type' => 'tool_result',
                    'toolUseID' => $block->id,
                    'content' => $result,
                ];
            }

            $messages[] = ['role' => 'user', 'content' => $toolResults];
        }
    }

    /**
     * Previous turns are replayed as plain user/assistant messages: only the final text of
     * each answer is stored, so the tool calls of past turns are not part of the history.
     *
     * @return array<int, array<string, mixed>>
     */
    private function messagesFrom(ConversationTurnCollection $history, UserMessage $userMessage): array
    {
        $messages = [];
        foreach ($history as $turn) {
            /** @var ConversationTurn $turn */
            $messages[] = ['role' => 'user', 'content' => $turn->messageWithContext()];
            $messages[] = ['role' => 'assistant', 'content' => $turn->agentResponse];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage->value()];

        return $messages;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function toolDefinitions(AgentToolCollection $tools): array
    {
        return array_map(
            fn (AgentTool $tool): array => [
                'name' => $tool->name(),
                'description' => $tool->description(),
                'inputSchema' => $tool->inputSchema(),
            ],
            $tools->items()
        );
    }

    /**
     * A failing tool is reported back to the model so it can keep answering, instead of
     * breaking a response that is already being streamed to the user.
     *
     * @param array<string, mixed> $input
     */
    private function executeTool(AgentToolCollection $tools, string $name, array $input): string
    {
        $tool = $tools->findByName($name);
        if ($tool === null) {
            return "La herramienta {$name} no existe.";
        }

        try {
            return $tool->execute($input);
        } catch (Throwable $e) {
            return "Error al ejecutar {$name}: {$e->getMessage()}";
        }
    }
}
