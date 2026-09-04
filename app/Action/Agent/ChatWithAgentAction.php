<?php

namespace Siesta\App\Action\Agent;

use Siesta\Agent\Application\Chat\ChatWithAgentRequest;
use Siesta\Agent\Application\Chat\ChatWithAgentUseCase;
use Siesta\App\Action\BaseAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatWithAgentAction extends BaseAction
{
    public function __construct(private readonly ChatWithAgentUseCase $chatWithAgentUseCase)
    {
    }

    public function __invoke(Request $request): Response
    {
        $userId = $request->headers->get('User-Id');
        $data = $request->toArray();
        $context = $data['context'] ?? null;

        $chatRequest = new ChatWithAgentRequest(
            $userId,
            $data['message'],
            $context['movie_title'] ?? null,
            $context['movie_year'] ?? null,
        );

        return new StreamedResponse(function () use ($chatRequest) {
            foreach ($this->chatWithAgentUseCase->execute($chatRequest) as $textChunk) {
                echo 'data: ' . json_encode(['text' => $textChunk], JSON_UNESCAPED_UNICODE) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        }, Response::HTTP_OK, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}
