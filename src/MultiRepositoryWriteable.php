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
    public function update(array $query): int
    {
        // Freeform query was detected
        if (isset($query['query']) || isset($query['parameters'])) {
            return $this->updateQueryFreeform($query);
        }

        // Regular structured query
        return $this->updateQuery($query);
    }

    private function updateQueryFreeform(array $query): int
    {
        // Process options and make sure all values are valid
        [$sanitizedOptions, $tableName, $objectToTableFields] = $this->processOptions([
            'repositories' => [],
            'query' => '',
            'parameters' => [],
        ], $query, true);

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

    private function updateQuery(array $query): int
    {
        // Process options and make sure all values are valid
        [$sanitizedOptions, $tableName, $objectToTableFields] = $this->processOptions([
            'repositories' => [],
            'tables' => [],
            'changes' => [],
            'where' => [],
            'order' => [],
            'limit' => 0,
        ], $query, true);

        // List of finished UPDATE expressions, to be imploded with , + possible query values
        $sanitizedOptions['tables'] = $this->preprocessJoins(
            $sanitizedOptions['tables'],
            $tableName,
            $objectToTableFields
        );

        // List of finished SET expressions, to be imploded with , + possible query values
        $sanitizedOptions['changes'] = $this->preprocessChanges($sanitizedOptions['changes'], $objectToTableFields);

        // List of finished WHERE expressions, to be imploded with ANDs
        $sanitizedOptions['where'] = $this->preprocessWhere($sanitizedOptions['where'], $objectToTableFields);

        // Order was defined
        if (isset($sanitizedOptions['order']) && \count($sanitizedOptions['order']) > 0) {
            $sanitizedOptions['order'] = $this->preprocessOrder($sanitizedOptions['order'], $objectToTableFields);
        } else {
            unset($sanitizedOptions['order']);
        }

        // No limit - remove it from options
        if ($sanitizedOptions['limit'] === 0) {
            unset($sanitizedOptions['limit']);
        }

        // Execute update query and return number of affected rows from the update
        try {
            return $this->db->update($sanitizedOptions);
        } catch (DBInvalidOptionException $e) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [MultiRepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage()
            );
        }
    }

    /**
     * Build update (SET) query part of all SQL queries
     *
     * @param array $changes
     * @param array $objectToTableFields
     * @return array
     */
    private function preprocessChanges(array $changes, array $objectToTableFields)
    {
        // List of finished SET expressions, to be imploded with ,
        $changesProcessed = [];

        // Go through table selection
        foreach ($changes as $expression => $values) {
            if (\is_int($expression)) {
                $expression = $values;
                $values = null;
            }

            // Expression always has to be a string
            if (!\is_string($expression)) {
                throw DBDebug::createException(
                    DBInvalidOptionException::class,
                    [MultiRepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Invalid "changes" / SET definition, expression is not a string: ' .
                    DBDebug::sanitizeData($expression)
                );
            }

            // No expression, only a table field name
            if (\strpos($expression, ':') === false) {
                // No variables and no values - we need one of either for a valid change
                if (!isset($values)) {
                    throw DBDebug::createException(
                        DBInvalidOptionException::class,
                        [MultiRepositoryReadOnlyInterface::class, ActionInterface::class],
                        'Invalid "changes" / SET definition, no value(s) for fixed variable ' .
                        DBDebug::sanitizeData($expression)
                    );
                }

                // Get separated table and field parts
                $fieldParts = \explode('.', $expression);

                // Field was not found
                if (\count($fieldParts) <= 1 || !isset($objectToTableFields[$fieldParts[0]][$fieldParts[1]])) {
                    throw DBDebug::createException(
                        DBInvalidOptionException::class,
                        [MultiRepositoryReadOnlyInterface::class, ActionInterface::class],
                        'Invalid "changes" / SET definition, field name was not found in repository: ' .
                        DBDebug::sanitizeData($expression)
                    );
                }

                // Convert field name
                $expression = $fieldParts[0] . '.' . $objectToTableFields[$fieldParts[0]][$fieldParts[1]];
            } else { // Freestyle expression
                // Replace all expressions of all involved repositories
                foreach ($objectToTableFields as $table => $tableFields) {
                    foreach ($tableFields as $objFieldName => $sqlFieldName) {
                        $expression = \str_replace(
                            ':' . $table . '.' . $objFieldName . ':',
                            $this->db->quoteIdentifier($table . '.' . $sqlFieldName),
                            $expression,
                            $count
                        );
                    }
                }

                // If we still have unresolved expressions, something went wrong
                if (\strpos($expression, ':') !== false) {
                    throw DBDebug::createException(
                        DBInvalidOptionException::class,
                        [MultiRepositoryReadOnlyInterface::class, ActionInterface::class],
                        'Unresolved colons in "changes" / SET clause: ' .
                        DBDebug::sanitizeData($expression)
                    );
                }
            }

            // Add change entry to the processed list
            if ($values === null) {
                $changesProcessed[] = $expression;
            } else {
                $changesProcessed[$expression] = $values;
            }
        }

        return $changesProcessed;
    }
}
