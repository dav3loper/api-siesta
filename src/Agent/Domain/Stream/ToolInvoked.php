<?php

namespace Siesta\Agent\Domain\Stream;

class ToolInvoked implements AgentEvent
{
    /**
     * @param array<string, mixed> $input
     */
    public function __construct(
        public readonly string $toolName,
        public readonly array $input,
        public readonly string $result,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tool' => $this->toolName,
            'input' => $this->input,
            'result' => $this->result,
        ];
    }
}
