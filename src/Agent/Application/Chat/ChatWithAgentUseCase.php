<?php

namespace Siesta\Agent\Application\Chat;

use Siesta\Agent\Domain\AgentClient;
use Siesta\Agent\Domain\RatedMovie;
use Siesta\Agent\Domain\UserProfile;
use Siesta\Agent\Domain\UserProfileRepository;
use Siesta\Shared\Id\Id;

class ChatWithAgentUseCase
{
    private const COMMUNICATIONS_DISABLED_MESSAGE = 'Las comunicaciones están apagadas por ahora...';

    public function __construct(
        private readonly UserProfileRepository $userProfileRepository,
        private readonly AgentClient $agentClient,
        private readonly bool $communicationsEnabled,
    )
    {
    }

    /**
     * @return iterable<string>
     */
    public function execute(ChatWithAgentRequest $request): iterable
    {
        if (!$this->communicationsEnabled) {
            yield self::COMMUNICATIONS_DISABLED_MESSAGE;
            return;
        }

        $profile = $this->userProfileRepository->getByUserId(new Id($request->userId));
        $systemPrompt = $this->buildSystemPrompt($profile, $request->movieTitle, $request->movieYear);

        yield from $this->agentClient->streamAnswer($systemPrompt, $request->message);
    }

    private function buildSystemPrompt(UserProfile $profile, ?string $movieTitle, ?int $movieYear): string
    {
        $lines = [];
        foreach ($profile->ratedMovies as $ratedMovie) {
            /** @var RatedMovie $ratedMovie */
            $parts = [];
            if ($ratedMovie->voteScore !== null) {
                $parts[] = "voto: {$ratedMovie->voteScore->name}";
            }
            if ($ratedMovie->ratingScore !== null) {
                $parts[] = "rating: {$ratedMovie->ratingScore}/5";
            }
            $lines[] = "- {$ratedMovie->title} (" . implode(', ', $parts) . ')';
        }

        $history = $lines === [] ? 'Sin historial todavía.' : implode("\n", $lines);

        $movieContext = '';
        if ($movieTitle !== null) {
            $movieContext = "\nEl usuario está viendo la ficha de la película \"{$movieTitle}\"";
            $movieContext .= $movieYear !== null ? " ({$movieYear})" : '';
            $movieContext .= ". Si pregunta por \"esta película\" sin más contexto, refiérete a esa.\n";
        }

        return <<<PROMPT
            Eres el asistente de recomendación de películas del Festival de Sitges. Ayudas al usuario a decidir si una película le puede gustar, basándote en su historial de gustos en la app.

            Historial del usuario (voto = si le interesaba verla; rating = qué le pareció tras verla, 1-5):
            {$history}
            {$movieContext}
            Responde en español, de forma breve y directa.
            PROMPT;
    }
}
