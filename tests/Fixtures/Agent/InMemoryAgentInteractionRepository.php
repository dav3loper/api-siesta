<?php

namespace Siesta\Tests\Fixtures\Agent;

use Siesta\Agent\Domain\ConversationTurnCollection;
use Siesta\Agent\Domain\Interaction\AgentInteraction;
use Siesta\Agent\Domain\Interaction\AgentInteractionRepository;
use Siesta\Agent\Domain\Interaction\InteractionStatus;
use Siesta\Shared\Date\Date;
use Siesta\Shared\Id\Id;

class InMemoryAgentInteractionRepository implements AgentInteractionRepository
{
    /** @var AgentInteraction[] */
    public array $saved = [];
    /** @var InteractionStatus[] the status each save was made with, since the entity keeps mutating */
    public array $savedStatuses = [];
    public int $interactionsInWindow = 0;
    public ConversationTurnCollection $history;
    /** @var array<string, mixed>|null the arguments the history was asked for */
    public ?array $historyQuery = null;

    public function __construct()
    {
        $this->history = new ConversationTurnCollection([]);
    }

    public function lastTurnsOfConversation(Id $userId, string $conversationId, int $maxTurns): ConversationTurnCollection
    {
        $this->historyQuery = [
            'userId' => $userId->id,
            'conversationId' => $conversationId,
            'maxTurns' => $maxTurns,
        ];

        return $this->history;
    }

    public function save(AgentInteraction $interaction): void
    {
        if ($interaction->id() === null) {
            $interaction->assignId(new Id((string)(count($this->saved) + 1)));
        }

        $this->saved[] = $interaction;
        $this->savedStatuses[] = $interaction->status();
    }

    public function countByUserSince(Id $userId, Date $since): int
    {
        return $this->interactionsInWindow;
    }

    public function last(): AgentInteraction
    {
        return $this->saved[count($this->saved) - 1];
    }
}
