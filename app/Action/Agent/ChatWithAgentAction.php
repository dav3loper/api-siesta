<?php

namespace Siesta\App\Action\Agent;

use Siesta\Agent\Application\Chat\ChatWithAgentRequest;
use Siesta\Agent\Application\Chat\ChatWithAgentUseCase;
use Siesta\Agent\Domain\Stream\TextChunk;
use Siesta\Agent\Domain\Stream\UnknownTitlesDetected;
use Siesta\Agent\Domain\UserMessage;
use Siesta\App\Action\BaseAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ChatWithAgentAction extends BaseAction
{
    public function __construct(private readonly ChatWithAgentUseCase $chatWithAgentUseCase)
    {
    }

    public function __invoke(Request $request): Response
    {
        $data = $request->toArray();
        $context = $data['context'] ?? null;

        $chatRequest = new ChatWithAgentRequest(
            $data['conversation_id'] ?? bin2hex(random_bytes(16)),
            $request->headers->get('User-Id'),
            $request->headers->get('Group-Id'),
            new UserMessage($data['message'] ?? ''),
            $context['movie_title'] ?? null,
            $context['movie_year'] ?? null,
        );

        $events = $this->chatWithAgentUseCase->execute($chatRequest);

        return new StreamedResponse(function () use ($events) {
            try {
                foreach ($events as $event) {
                    if ($event instanceof TextChunk) {
                        $this->send('message', ['text' => $event->text]);
                        continue;
                    }

                    if ($event instanceof UnknownTitlesDetected) {
                        $this->send('warning', ['unknown_titles' => $event->titles]);
                    }
                }

                $this->send('done', []);
            } catch (Throwable $e) {
                // The 200 and the SSE headers are already sent, so the only way left to tell
                // the client something went wrong is an event of its own.
                $this->send('error', ['error' => $e->getMessage()]);
            }
        }, Response::HTTP_OK, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function send(string $event, array $payload): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
