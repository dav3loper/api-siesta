<?php

namespace Siesta\Agent\Domain\Stream;

class UnknownTitlesDetected implements AgentEvent
{
    /**
     * @param string[] $titles
     */
    public function __construct(public readonly array $titles)
    {
    }
}
