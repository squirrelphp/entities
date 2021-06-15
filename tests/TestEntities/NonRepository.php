<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\PopulatePropertiesWithIterableTrait;

class NonRepository
{
    use PopulatePropertiesWithIterableTrait;

    private int $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
