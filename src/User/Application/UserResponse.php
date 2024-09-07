<?php

namespace Siesta\User\Application;

use Siesta\Shared\Id\Id;
use Siesta\User\Domain\User;
use Siesta\User\Domain\UserCollection;

class UserResponse implements \JsonSerializable
{

    public function __construct(public readonly Id $groupId, public readonly UserCollection $userCollection)
    {
    }


    public function jsonSerialize(): mixed
    {
        $userDataList = array_map(fn(User $user) => ['name' => $user->name, 'id' => $user->id->id], $this->userCollection->items());
        $groupName = $this->userCollection->items()[0]->group->name;
        return [
            'id' => $this->groupId->id,
            'name' => $groupName,
            'user_list' => $userDataList
        ];
    }
}