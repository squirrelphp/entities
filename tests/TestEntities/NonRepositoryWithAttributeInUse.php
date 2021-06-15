<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\Attribute as SQL;
use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;
use Squirrel\Entities\PopulatePropertiesWithIterableTrait;

class NonRepositoryWithAttributeInUse
{
    use PopulatePropertiesWithIterableTrait;

    private int $userId = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }
}
