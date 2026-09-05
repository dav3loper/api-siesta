<?php

namespace Siesta\Agent\Domain\Interaction;

use Siesta\Shared\Date\Date;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;

interface AgentInteractionRepository
{
    /**
     * Inserts the interaction the first time and updates it afterwards.
     *
     * @throws InternalError
     */
    public function save(AgentInteraction $interaction): void;

    /**
     * @throws InternalError
     */
    public function countByUserSince(Id $userId, Date $since): int;
}
