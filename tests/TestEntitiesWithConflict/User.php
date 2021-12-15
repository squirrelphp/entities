<?php

namespace Squirrel\Entities\Tests\TestEntitiesWithConflict;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;

#[Entity("users", connection: "dada")]
class User
{
    #[Field("user_id", autoincrement: true)]
    private int $userId = 0;

    #[Field("active")]
    private bool $active = false;

    #[Field("user_name")]
    private string $userName = '';

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }
}
