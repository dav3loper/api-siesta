<?php

namespace Siesta\Agent\Domain\Stream;

class TextChunk implements AgentEvent
{
    public function __construct(public readonly string $text)
    {
    }
}
