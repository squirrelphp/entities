<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Entities\RepositorySelectQueryInterface;

class TicketMessageRepositoryReadOnlyDifferentRepositoryVariableWithin implements RepositoryReadOnlyInterface
{
    /**
     * @inheritDoc
     */
    public function select(array $query): RepositorySelectQueryInterface
    {
        return \Mockery::mock(RepositorySelectQueryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function fetch(RepositorySelectQueryInterface $selectQuery)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function clear(RepositorySelectQueryInterface $selectQuery): void
    {
    }

    /**
     * @inheritDoc
     */
    public function fetchOne(array $query)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(array $query)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function count(array $query): int
    {
        return 6;
    }
}
