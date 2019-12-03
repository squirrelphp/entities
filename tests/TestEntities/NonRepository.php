<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\EntityConstructorTrait;

class NonRepository
{
    use EntityConstructorTrait;

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
