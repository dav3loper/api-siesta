<?php

namespace Siesta\User\Domain;

use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Exception\ValueNotValid;
use Siesta\Shared\ValueObject\Email;

interface UserRepository
{
    /**
     * @throws DataNotFound
     * @throws ValueNotValid
     * @throws InternalError
     */
    public function findByEmail(Email $email): User;
}