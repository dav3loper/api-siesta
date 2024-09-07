<?php

namespace Siesta\User\Infrastructure;

use Doctrine\DBAL\Connection;
use Siesta\Shared\Exception\DataNotFound;
use Siesta\Shared\Exception\InternalError;
use Siesta\Shared\Exception\ValueNotValid;
use Siesta\Shared\Id\Id;
use Siesta\Shared\ValueObject\Email;
use Siesta\Shared\ValueObject\Password;
use Siesta\User\Domain\Group;
use Siesta\User\Domain\User;
use Siesta\User\Domain\UserCollection;
use Siesta\User\Domain\UserRepository;
use Throwable;

class DoctrineUserRepository implements UserRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws DataNotFound
     * @throws ValueNotValid
     * @throws InternalError
     */
    public function findByEmail(Email $email): User
    {
        try {
            $data = $this->connection->createQueryBuilder()
                ->select('u.*, g.name as group_name')
                ->from('user', "u")
                ->leftJoin('u', "`group`", 'g', 'u.group_id = g.id')
                ->where('email = :email')
                ->setParameter('email', $email->getEmail())
                ->fetchAssociative();
        }catch (Throwable $e){
            throw new InternalError($e->getMessage());
        }
        if(empty($data)) {
            throw new DataNotFound('User not found');
        }

        return new User(
            new Id($data['id']),
            new Email($data['email']),
            $data['name'],
            Password::encoded($data['password']),
            $data['group_id'] ? new Group(new Id($data['group_id']), $data['group_name']) : null
        );
    }

    /**
     * @throws DataNotFound
     * @throws ValueNotValid
     * @throws InternalError
     */
    public function findByGroupId(Id $groupId): UserCollection
    {
        try {
            $data = $this->connection->createQueryBuilder()
                ->select('u.*, g.name as group_name')
                ->from('user', "u")
                ->leftJoin('u', "`group`", 'g', 'u.group_id = g.id')
                ->where('group_id = :groupId')
                ->setParameter('groupId', $groupId->id)
                ->fetchAllAssociative();
        }catch (Throwable $e){
            throw new InternalError($e->getMessage());
        }
        if(empty($data)) {
            throw new DataNotFound('User not found');
        }

        $userList = array_map(fn($userData) =>
            new User(
            new Id($userData['id']),
            new Email($userData['email']),
            $userData['name'],
            Password::encoded($userData['password']),
            $userData['group_id'] ? new Group(new Id($userData['group_id']), $userData['group_name']) : null
        ), $data);
        return new UserCollection($userList);
    }
}