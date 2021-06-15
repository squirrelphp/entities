<?php

namespace Squirrel\Entities\Tests\TestEntitiesInvalidFieldUnionType;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;
use Squirrel\Entities\PopulatePropertiesWithIterableTrait;

#[Entity("dada")]
class UserAddressInvalid
{
    use PopulatePropertiesWithIterableTrait;

    /**
     * PHP8 union types are not allowed
     */
    #[Field("user_id")]
    private int|string $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
