<?php

namespace Siesta\Agent\Domain\Tool;

interface AgentTool
{
    public function name(): string;

    public function description(): string;

    /**
     * JSON schema of the accepted input, as the model expects it.
     *
     * @return array<string, mixed>
     */
    public function inputSchema(): array;

    /**
     * @param array<string, mixed> $input
     */
    public function execute(array $input): string;
}
