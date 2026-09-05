<?php

namespace Siesta\Agent\Domain;

class ConversationTurn
{
    public function __construct(
        public readonly UserMessage $userMessage,
        public readonly string $agentResponse,
        public readonly ?string $movieTitle,
        public readonly ?int $movieYear,
    )
    {
    }

    /**
     * The message as the model should read it again. Which movie sheet the user had open is
     * part of what a turn meant: the current one travels in the system prompt, but a past turn
     * carries its own, and without it "¿me va a gustar?" looks like it was about the movie
     * being asked about now.
     */
    public function messageWithContext(): string
    {
        if ($this->movieTitle === null) {
            return $this->userMessage->value();
        }

        $movie = $this->movieYear !== null
            ? "{$this->movieTitle} ({$this->movieYear})"
            : $this->movieTitle;

        return "[ficha abierta: {$movie}] {$this->userMessage->value()}";
    }
}
