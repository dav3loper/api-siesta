<?php

namespace Siesta\Agent\Application\Chat;

class ChatWithAgentRequest
{
    public function __construct(
        public readonly string $userId,
        public readonly string $message,
        public readonly ?string $movieTitle,
        public readonly ?int $movieYear,
    )
    {
    }
}
