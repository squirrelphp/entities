<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Entities\RepositorySelectQueryInterface;

class TicketMessageRepositoryReadOnlyDifferentRepositoryVariableWithin implements RepositoryReadOnlyInterface
{
    public function select(array $query): RepositorySelectQueryInterface
    {
        return \Mockery::mock(RepositorySelectQueryInterface::class);
    }

    public function fetch(RepositorySelectQueryInterface $selectQuery): ?object
    {
        return new \stdClass();
    }

    public function clear(RepositorySelectQueryInterface $selectQuery): void
    {
    }

    public function fetchOne(array $query): ?object
    {
        return new \stdClass();
    }

    public function fetchAll(array $query): array
    {
        return [new \stdClass()];
    }

    public function fetchAllAndFlatten(array $query): array
    {
        return [5, 10, 13];
    }

    public function count(array $query): int
    {
        return 6;
    }
}
