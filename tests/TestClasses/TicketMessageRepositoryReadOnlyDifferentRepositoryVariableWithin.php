<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\RepositoryReadOnlyInterface;

class TicketMessageRepositoryReadOnlyDifferentRepositoryVariableWithin implements RepositoryReadOnlyInterface
{
    /**
     * @inheritDoc
     */
    public function select(array $query): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function selectOne(array $query)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function selectFlattenedFields(array $query): array
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
