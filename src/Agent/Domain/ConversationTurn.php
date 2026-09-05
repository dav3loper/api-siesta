<?php

namespace Siesta\Agent\Domain;

class ConversationTurn
{
    public function __construct(
        public readonly UserMessage $userMessage,
        public readonly string $agentResponse,
    )
    {
    }
}
