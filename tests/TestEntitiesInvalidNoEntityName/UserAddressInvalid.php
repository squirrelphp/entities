<?php

namespace Squirrel\Entities\Tests\TestEntitiesInvalidNoEntityName;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;

/**
 * An empty name is not allowed
 */
#[Entity("")]
class UserAddressInvalid
{
    #[Field("user_id")]
    private int $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
