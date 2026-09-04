<?php

namespace Siesta\Agent\Domain;

use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Id\Id;

interface UserProfileRepository
{
    /**
     * @throws InternalError
     */
    public function getByUserId(Id $userId): UserProfile;
}
