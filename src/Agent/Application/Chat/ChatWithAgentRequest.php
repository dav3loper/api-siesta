<?php

namespace Siesta\Agent\Application\Chat;

use Siesta\Agent\Domain\UserMessage;

class ChatWithAgentRequest
{
    public function __construct(
        public readonly string $conversationId,
        public readonly string $userId,
        public readonly ?string $groupId,
        public readonly UserMessage $message,
        public readonly ?string $movieTitle,
        public readonly ?int $movieYear,
    )
    {
    }
}
