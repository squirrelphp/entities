<?php

namespace Squirrel\Entities\Tests\TestEntities;

class NonRepository
{
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
