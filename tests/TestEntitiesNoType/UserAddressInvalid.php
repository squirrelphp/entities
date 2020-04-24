<?php

namespace Squirrel\Entities\Tests\TestEntitiesNoType;

use Squirrel\Entities\Annotation\Entity;
use Squirrel\Entities\Annotation\Field;
use Squirrel\Entities\EntityConstructorTrait;

/**
 * @Entity("users_address")
 */
class UserAddressInvalid
{
    use EntityConstructorTrait;

    /**
     * No property type defined - we need this
     *
     * @Field("user_id")
     */
    private $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
