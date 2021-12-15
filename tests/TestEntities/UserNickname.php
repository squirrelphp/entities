<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\Attribute\{Entity, Field};

#[Entity("users_address")]
class UserNickname
{
    #[Field("user_id")]
    private int $userId = 0;

    #[Field("user_name")]
    private string $userName = "";

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }
}
