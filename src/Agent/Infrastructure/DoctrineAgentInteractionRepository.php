<?php

namespace Siesta\Agent\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Agent\Domain\ConversationTurn;
use Siesta\Agent\Domain\ConversationTurnCollection;
use Siesta\Agent\Domain\Interaction\AgentInteraction;
use Siesta\Agent\Domain\Interaction\AgentInteractionRepository;
use Siesta\Agent\Domain\Interaction\InteractionStatus;
use Siesta\Agent\Domain\Stream\ToolInvoked;
use Siesta\Agent\Domain\UserMessage;
use Siesta\Shared\Date\Date;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;
use Throwable;

class DoctrineAgentInteractionRepository implements AgentInteractionRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws InternalError
     */
    public function save(AgentInteraction $interaction): void
    {
        $id = $interaction->id();

        try {
            if ($id === null) {
                $this->connection->insert('agent_interaction', array_merge($this->toRow($interaction), [
                    'created_at' => new Date('now'),
                    'updated_at' => new Date('now'),
                ]));
                $interaction->assignId(new Id((string)$this->connection->lastInsertId()));
                return;
            }

            $this->connection->update(
                'agent_interaction',
                array_merge($this->toRow($interaction), ['updated_at' => new Date('now')]),
                ['id' => $id->id]
            );
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }
    }

    /**
     * @throws InternalError
     */
    public function lastTurnsOfConversation(Id $userId, string $conversationId, int $maxTurns): ConversationTurnCollection
    {
        try {
            $rows = $this->connection->createQueryBuilder()
                ->select('user_message', 'agent_response', 'movie_title', 'movie_year')
                ->from('agent_interaction')
                ->where('user_id=:userId')
                ->andWhere('conversation_id=:conversationId')
                ->andWhere('status=:status')
                ->andWhere('agent_response IS NOT NULL')
                ->andWhere("agent_response<>''")
                ->orderBy('id', 'DESC')
                ->setMaxResults($maxTurns)
                ->setParameter('userId', $userId)
                ->setParameter('conversationId', $conversationId)
                ->setParameter('status', InteractionStatus::COMPLETED->value)
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        $turns = array_map(
            fn (array $row): ConversationTurn => new ConversationTurn(
                new UserMessage($row['user_message']),
                $row['agent_response'],
                $row['movie_title'],
                $row['movie_year'] !== null ? (int)$row['movie_year'] : null
            ),
            array_reverse($rows)
        );

        return new ConversationTurnCollection($turns);
    }

    /**
     * @throws InternalError
     */
    public function countByUserSince(Id $userId, Date $since): int
    {
        try {
            $count = $this->connection->createQueryBuilder()
                ->select('COUNT(id)')
                ->from('agent_interaction')
                ->where('user_id=:userId')
                ->andWhere('created_at>=:since')
                ->setParameter('userId', $userId)
                ->setParameter('since', (string)$since)
                ->fetchOne();
        } catch (Throwable $e) {
            throw new InternalError($e->getMessage());
        }

        return (int)$count;
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(AgentInteraction $interaction): array
    {
        $toolCalls = array_map(
            fn (ToolInvoked $toolCall): array => $toolCall->toArray(),
            $interaction->toolCalls()
        );

        return [
            'conversation_id' => $interaction->conversationId,
            'user_id' => $interaction->userId->id,
            'group_id' => $interaction->groupId?->id,
            'movie_title' => $interaction->movieTitle,
            'movie_year' => $interaction->movieYear,
            'user_message' => $interaction->message->value(),
            'agent_response' => $interaction->response(),
            'tool_calls' => $toolCalls === [] ? null : json_encode($toolCalls, JSON_UNESCAPED_UNICODE),
            'unknown_titles' => $interaction->unknownTitles() === []
                ? null
                : json_encode($interaction->unknownTitles(), JSON_UNESCAPED_UNICODE),
            'status' => $interaction->status()->value,
            'error' => $interaction->error(),
            'model' => $interaction->model,
        ];
    }
}
