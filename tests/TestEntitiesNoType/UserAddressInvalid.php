<?php

namespace Squirrel\Entities\Tests\TestEntitiesNoType;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;

#[Entity("users_address")]
class UserAddressInvalid
{
    /**
     * No property type defined - we need this
     */
    #[Field("user_id")]
    private $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
