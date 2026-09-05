<?php

namespace Siesta\Agent\Domain;

use Siesta\Agent\Domain\Stream\AgentEvent;
use Siesta\Agent\Domain\Tool\AgentToolCollection;
use Siesta\Shared\Exception\InternalError;

interface AgentClient
{
    /**
     * @return iterable<AgentEvent>
     *
     * @throws InternalError
     */
    public function streamAnswer(string $systemPrompt, UserMessage $userMessage, AgentToolCollection $tools): iterable;
}
