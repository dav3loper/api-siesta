<?php

namespace Siesta\Agent\Domain\Interaction;

enum InteractionStatus: string
{
    case STARTED = 'started';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REJECTED = 'rejected';
}
