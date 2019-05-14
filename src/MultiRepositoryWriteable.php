<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Action\ActionInterface;
use Squirrel\Queries\DBDebug;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * QueryHandler functionality: If more than one table needs to be selected or updated
 * at once QueryHandler combines the knowledge of multiple Repository classes to create
 * a query which is simple and secure
 */
class MultiRepositoryWriteable extends MultiRepositoryReadOnly implements MultiRepositoryWriteableInterface
{
    /**
     * @inheritdoc
     */
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
        } catch (DBInvalidOptionException $e) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [MultiRepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage()
            );
        }
    }
}
