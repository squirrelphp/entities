<?php

namespace Squirrel\Entities;

use Squirrel\Debug\Debug;
use Squirrel\Queries\Builder\BuilderInterface;
use Squirrel\Queries\DBException;

/**
 * If more than one table needs to be selected or updated at once this class
 * combines the knowledge of multiple Repository classes to create
 * a query which is simple and secure
 */
class MultiRepositoryWriteable extends MultiRepositoryReadOnly implements MultiRepositoryWriteableInterface
{
    public function update(array $repositories, string $query, array $parameters = []): int
    {
        // Process options and make sure all values are valid
        [$sanitizedOptions, $tableName, $objectToTableFields] = $this->processOptions([
            'repositories' => [],
            'query' => '',
            'parameters' => [],
        ], [
            'repositories' => $repositories,
            'query' => $query,
            'parameters' => $parameters,
        ], true);

        // Process the query
        $sqlQuery = $this->buildFreeform($sanitizedOptions['query'], $tableName, $objectToTableFields);

        // Execute update query and return number of affected rows from the update
        try {
            return $this->db->change($sqlQuery, $sanitizedOptions['parameters']);
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                $e->getMessage(),
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                previousException: $e->getPrevious(),
            );
        }
    }
}
