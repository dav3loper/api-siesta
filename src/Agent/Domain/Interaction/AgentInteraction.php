<?php

namespace Siesta\Agent\Domain\Interaction;

use Siesta\Agent\Domain\Stream\ToolInvoked;
use Siesta\Agent\Domain\UserMessage;
use Siesta\Shared\Id\Id;

class AgentInteraction
{
    private ?Id $id = null;
    private InteractionStatus $status;
    private ?string $response = null;
    private ?string $error = null;
    /** @var ToolInvoked[] */
    private array $toolCalls = [];
    /** @var string[] */
    private array $unknownTitles = [];

    public function __construct(
        public readonly string $conversationId,
        public readonly Id $userId,
        public readonly ?Id $groupId,
        public readonly UserMessage $message,
        public readonly ?string $movieTitle,
        public readonly ?int $movieYear,
        public readonly string $model,
    )
    {
        $this->status = InteractionStatus::STARTED;
    }

    /**
     * @param ToolInvoked[] $toolCalls
     * @param string[] $unknownTitles
     */
    public function complete(string $response, array $toolCalls, array $unknownTitles): void
    {
        $this->status = InteractionStatus::COMPLETED;
        $this->response = $response;
        $this->toolCalls = $toolCalls;
        $this->unknownTitles = $unknownTitles;
    }

    public function fail(string $error): void
    {
        $this->status = InteractionStatus::FAILED;
        $this->error = $error;
    }

    public function reject(string $reason): void
    {
        $this->status = InteractionStatus::REJECTED;
        $this->error = $reason;
    }

    public function assignId(Id $id): void
    {
        $this->id = $id;
    }

    public function id(): ?Id
    {
        return $this->id;
    }

    public function status(): InteractionStatus
    {
        return $this->status;
    }

    public function response(): ?string
    {
        return $this->response;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    /**
     * @return ToolInvoked[]
     */
    public function toolCalls(): array
    {
        return $this->toolCalls;
    }

    /**
     * @return string[]
     */
    public function unknownTitles(): array
    {
        return $this->unknownTitles;
    }
}
