<?php

namespace Squirrel\Entities;

/**
 * INTERFACE: Multiple repositories should be written to (UPDATE) or read from (SELECT)
 */
interface MultiRepositoryWriteableInterface extends MultiRepositoryReadOnlyInterface
{
    /**
     * Update query - only custom freeform query are possible
     *
     * Freeform queries should almost never be necessary and is considered bad practice, because they
     * are not compatible across different database systems (different syntax, different options,
     * different behavior) yet it is still a possibility because some queries might
     * still be necessary or useful for performance or other reasons
     *
     * @param array $repositories
     * @param string $query
     * @param array $parameters
     * @return int
     */
    public function update(array $repositories, string $query, array $parameters = []): int;
}
