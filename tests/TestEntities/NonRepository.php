<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\PopulatePropertiesWithIterableTrait;

class NonRepository
{
    use PopulatePropertiesWithIterableTrait;

    /**
     * @var int
     */
    private $userId = 0;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}
