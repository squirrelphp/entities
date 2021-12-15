<?php

namespace Squirrel\Entities\Tests\TestEntitiesInvalidBlob;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;

#[Entity("users_address")]
class UserAddressInvalid
{
    /**
     * An integer field cannot be a blob: (only strings can be a blob)
     */
    #[Field("user_id", blob: true)]
    private int $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
