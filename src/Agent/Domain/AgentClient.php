<?php

namespace Siesta\Agent\Domain;

interface AgentClient
{
    /**
     * @return iterable<string>
     */
    public function streamAnswer(string $systemPrompt, string $userMessage): iterable;
}
