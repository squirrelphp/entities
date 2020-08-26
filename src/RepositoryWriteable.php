<?php

namespace Squirrel\Entities;

use Squirrel\Debug\Debug;
use Squirrel\Queries\Builder\BuilderInterface;
use Squirrel\Queries\DBException;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Repository functionality: Get data from one table and change data in that one table
 * through narrowly defined functions, leading to simple, secure and fast queries
 */
class RepositoryWriteable extends RepositoryReadOnly implements RepositoryWriteableInterface
{
    public function update(array $changes, array $where): int
    {
        // We need fields to update, otherwise there is nothing to do
        if (\count($changes) === 0) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, BuilderInterface::class],
                'No "changes" / SET clause defined',
            );
        }

        $where = $this->preprocessWhere($where);
        $changes = $this->preprocessChanges($changes);

        try {
            // Execute the query
            return $this->db->update($this->config->getTableName(), $changes, $where);
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                [RepositoryReadOnlyInterface::class, BuilderInterface::class],
                $e->getMessage(),
                $e->getPrevious(),
            );
        }
    }

    /**
     * Build update (SET) query part of all SQL queries
     *
     * @param array<int|string,mixed> $changes
     * @return array<int|string,mixed>
     *
     * @throws DBInvalidOptionException
     */
    private function preprocessChanges(array $changes): array
    {
        // Separate field SQL and field values
        $changesProcessed = [];

        // Go through the fields
        foreach ($changes as $fieldName => $fieldValue) {
            // Freestyle update clause
            if (\is_int($fieldName)) {
                $fieldName = $fieldValue;
                $fieldValue = [];
            }

            // Make sure we have a valid fieldname
            if (!\is_string($fieldName)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, BuilderInterface::class],
                    'Invalid "changes" / SET definition, expression is not a string: ' .
                    Debug::sanitizeData($fieldName),
                );
            }

            // No variables are contained in SQL
            if (\strpos($fieldName, ':') === false) {
                $fieldValue = $this->castOneTableVariable($fieldValue, $fieldName, true);
                $fieldName = $this->convertNameToTable($fieldName);
            } else { // Variables are contained in SQL
                // Cast change values - can be scalar or array
                $fieldValue = $this->castTableVariable($fieldValue);

                // Convert all :variable: values from object to table notation
                $fieldName = $this->convertNamesToTableInString($fieldName);

                // Variables still exist which were not resolved
                if (\strpos($fieldName, ':') !== false) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        [RepositoryReadOnlyInterface::class, BuilderInterface::class],
                        'Unresolved colons in "changes" / SET clause: ' .
                        Debug::sanitizeData($fieldName),
                    );
                }
            }

            // Add change entry to the processed list
            if (\is_array($fieldValue) && \count($fieldValue) === 0) {
                $changesProcessed[] = $fieldName;
            } else {
                $changesProcessed[$fieldName] = $fieldValue;
            }
        }

        return $changesProcessed;
    }

    public function insert(array $fields, bool $returnInsertId = false): ?string
    {
        // Make sure we have an autoincrement field if one is requested
        if ($returnInsertId === true && \strlen($this->config->getAutoincrementField()) === 0) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, BuilderInterface::class],
                'Insert ID requested but no autoincrement ID specified: ' .
                Debug::sanitizeData($fields),
            );
        }

        // Insert fields with converted field names
        $actualFields = [];

        // Convert all the field names from object to table
        foreach ($fields as $fieldName => $fieldValue) {
            $actualFields[$this->convertNameToTable($fieldName)] = $this->castOneTableVariable(
                $fieldValue,
                $fieldName,
                true,
            );
        }

        try {
            // Delegate insert to DBAL
            if ($returnInsertId === true) {
                return $this->db->insert(
                    $this->config->getTableName(),
                    $actualFields,
                    $this->config->getAutoincrementField(),
                );
            }

            $this->db->insert($this->config->getTableName(), $actualFields);
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                [RepositoryReadOnlyInterface::class, BuilderInterface::class],
                $e->getMessage(),
                $e->getPrevious(),
            );
        }

        // Return null if no insert ID is requested
        return null;
    }

    public function insertOrUpdate(array $fields, array $indexFields = [], ?array $updateFields = null): void
    {
        // Fields after conversion to table notation
        $actualIndexFields = [];

        // Convert the index field names from object to table
        foreach ($indexFields as $fieldName) {
            if (!isset($fields[$fieldName])) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, BuilderInterface::class],
                    'Index field specified do not occur in data array: ' .
                    Debug::sanitizeData($fieldName),
                );
            }

            $actualIndexFields[] = $this->convertNameToTable($fieldName);
        }

        // Insert fields with converted field names
        $actualFields = [];

        // Convert all the field names from object to table
        foreach ($fields as $fieldName => $fieldValue) {
            $actualFields[$this->convertNameToTable($fieldName)] = $this->castOneTableVariable(
                $fieldValue,
                $fieldName,
                true,
            );
        }

        if (isset($updateFields)) {
            // Processed update array
            $actualUpdateFields = [];

            // Process the update part of the query
            foreach ($updateFields as $fieldName => $fieldValue) {
                // Freestyle update clause - make the object-to-table notation conversion
                if (\is_int($fieldName)) {
                    $actualUpdateFields[] = $this->convertNamesToTableInString($fieldValue);
                    continue;
                }

                // Structured update clause - convert table name and cast the value
                $actualUpdateFields[$this->convertNameToTable($fieldName)] = $this->castOneTableVariable(
                    $fieldValue,
                    $fieldName,
                    true,
                );
            }
        }

        try {
            // Call the upsert function with adjusted values
            $this->db->insertOrUpdate(
                $this->config->getTableName(),
                $actualFields,
                $actualIndexFields,
                $actualUpdateFields ?? null,
            );
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                [RepositoryReadOnlyInterface::class, BuilderInterface::class],
                $e->getMessage(),
                $e->getPrevious(),
            );
        }
    }

    public function delete(array $where): int
    {
        // Generate the WHERE part of the query
        $where = $this->preprocessWhere($where);

        try {
            // Execute the query
            return $this->db->delete($this->config->getTableName(), $where);
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                [RepositoryReadOnlyInterface::class, BuilderInterface::class],
                $e->getMessage(),
                $e->getPrevious(),
            );
        }
    }
}
