<?php

namespace Squirrel\Entities\Tests\TestEntitiesInvalidBlob;

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
     * An integer field cannot be a blob: (only strings can be a blob)
     *
     * @Field("user_id", blob=true)
     */
    private int $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
