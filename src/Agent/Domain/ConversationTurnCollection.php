<?php

namespace Siesta\Agent\Domain;

use Siesta\Shared\Collection\Collection;

class ConversationTurnCollection extends Collection
{
    protected function type(): string
    {
        return ConversationTurn::class;
    }
}
