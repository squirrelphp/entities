<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;

class NonRepositoryWithAttributeInUse
{
    private int $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
