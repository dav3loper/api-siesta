<?php

namespace Siesta\Agent\Domain\Interaction;

use Siesta\Agent\Domain\ConversationTurnCollection;
use Siesta\Shared\Date\Date;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;

interface AgentInteractionRepository
{
    /**
     * The last completed turns of a conversation, oldest first, so the agent can be given
     * back what was already said. Scoped by user on purpose: the conversation id travels in
     * the request, so without it anyone could read another user's conversation.
     *
     * @throws InternalError
     */
    public function lastTurnsOfConversation(Id $userId, string $conversationId, int $maxTurns): ConversationTurnCollection;

    /**
     * Inserts the interaction the first time and updates it afterwards.
     *
     * @throws InternalError
     */
    public function save(AgentInteraction $interaction): void;

    /**
     * @throws InternalError
     */
    public function countByUserSince(Id $userId, Date $since): int;
}
