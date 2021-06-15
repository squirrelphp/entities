<?php

namespace Squirrel\Entities\Tests\TestEntitiesInvalidNoFieldName;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;
use Squirrel\Entities\PopulatePropertiesWithIterableTrait;

#[Entity("users_address")]
class UserAddressInvalid
{
    use PopulatePropertiesWithIterableTrait;

    /**
     * An empty name is not allowed
     */
    #[Field("")]
    private int $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
