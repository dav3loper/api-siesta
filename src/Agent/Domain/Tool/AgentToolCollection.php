<?php

namespace Siesta\Agent\Domain\Tool;

use Siesta\Shared\Collection\Collection;

class AgentToolCollection extends Collection
{
    protected function type(): string
    {
        return AgentTool::class;
    }

    public function findByName(string $name): ?AgentTool
    {
        foreach ($this->items() as $tool) {
            /** @var AgentTool $tool */
            if ($tool->name() === $name) {
                return $tool;
            }
        }

        return null;
    }
}
