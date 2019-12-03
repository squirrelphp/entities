<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\Annotation as SQL;
use Squirrel\Entities\EntityConstructorTrait;

class NonRepositoryWithAnnotationInUse
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
