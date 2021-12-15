<?php

namespace Squirrel\Entities\Tests\TestEntities;

#[\Squirrel\Entities\Attribute\Entity("users_address")]
class UserName
{
    #[\Squirrel\Entities\Attribute\Field("user_id")]
    private int $userId = 0;

    #[\Squirrel\Entities\Attribute\Field("user_name")]
    private string $userName = '';

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }
}
