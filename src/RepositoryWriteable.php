<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Action\ActionInterface;
use Squirrel\Queries\DBDebug;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Repository functionality: Get data from one table and change data in that one table
 * through narrowly defined functions, leading to simple, secure and fast queries
 */
class RepositoryWriteable extends RepositoryReadOnly implements RepositoryWriteableInterface
{
    /**
     * @inheritDoc
     */
    public function update(array $query): int
    {
        // Process options and make sure all values are valid
        $sanitizedQuery = $this->processOptions([
            'changes' => [],
            'where' => [],
            'order' => [],
            'limit' => 0,
        ], $query);

        // We need specific WHERE restrictions, otherwise there is a huge risk
        // of overwriting to many entries
        if (\count($sanitizedQuery['where']) === 0) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'No restricting "where" defined'
            );
        }

        // We need fields to update, otherwise there is nothing to do
        if (\count($sanitizedQuery['changes']) === 0) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'No "changes" / SET clause defined'
            );
        }

        $sanitizedQuery['where'] = $this->preprocessWhere($sanitizedQuery['where']);
        $sanitizedQuery['changes'] = $this->preprocessChanges($sanitizedQuery['changes']);

        // Order part of the query was defined
        if (\count($sanitizedQuery['order']) > 0) {
            $sanitizedQuery['order'] = $this->preprocessOrder($sanitizedQuery['order']);
        } else {
            unset($sanitizedQuery['order']);
        }

        // No limit - remove it from options
        if ($sanitizedQuery['limit'] === 0) {
            unset($sanitizedQuery['limit']);
        }

        $sanitizedQuery['table'] = $this->config->getTableName();

        try {
            // Execute the query
            return $this->db->update($sanitizedQuery);
        } catch (DBInvalidOptionException $e) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage()
            );
        }
    }

    /**
     * Build update (SET) query part of all SQL queries
     *
     * @param array $changes
     * @return array
     *
     * @throws DBInvalidOptionException
     */
    private function preprocessChanges(array $changes)
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
                throw DBDebug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Invalid "changes" / SET definition, expression is not a string: ' .
                    DBDebug::sanitizeData($fieldName)
                );
            }

            // No variables are contained in SQL
            if (\strpos($fieldName, ':') === false) {
                $fieldValue = $this->castOneTableVariable($fieldValue, $fieldName);
                $fieldName = $this->convertNameToTable($fieldName);
            } else { // Variables are contained in SQL
                // Cast change values - can be scalar or array
                $fieldValue = $this->castTableVariable($fieldValue);

                // Convert all :variable: values from object to table notation
                $fieldName = $this->convertNamesToTableInString($fieldName);

                // Variables still exist which were not resolved
                if (\strpos($fieldName, ':') !== false) {
                    throw DBDebug::createException(
                        DBInvalidOptionException::class,
                        [RepositoryReadOnlyInterface::class, ActionInterface::class],
                        'Unresolved colons in "changes" / SET clause: ' .
                        DBDebug::sanitizeData($fieldName)
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

    /**
     * @inheritDoc
     */
    public function insert(array $fields, bool $returnInsertId = false): ?string
    {
        // Insert fields with converted field names
        $actualFields = [];

        // Convert all the field names from object to table
        foreach ($fields as $fieldName => $fieldValue) {
            $actualFields[$this->convertNameToTable($fieldName)] = $this->castOneTableVariable(
                $fieldValue,
                $fieldName
            );
        }

        try {
            // Delegate insert to DBAL
            $this->db->insert($this->config->getTableName(), $actualFields);
        } catch (DBInvalidOptionException $e) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage()
            );
        }

        // Return insert ID if requested
        if ($returnInsertId === true) {
            return $this->db->lastInsertId();
        }

        // Return null if no insert ID is requested
        return null;
    }

    /**
     * @inheritDoc
     */
    public function insertOrUpdate(array $fields, array $indexFields = [], array $updateFields = []): string
    {
        // Fields after conversion to table notation
        $actualIndexFields = [];

        // Convert the index field names from object to table
        foreach ($indexFields as $fieldName) {
            if (!isset($fields[$fieldName])) {
                throw DBDebug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Index field specified do not occur in data array: ' .
                    DBDebug::sanitizeData($fieldName)
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
                $fieldName
            );
        }

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
                $fieldName
            );
        }

        try {
            // Call the upsert function with adjusted values
            $result = $this->db->upsert(
                $this->config->getTableName(),
                $actualFields,
                $actualIndexFields,
                $actualUpdateFields
            );
        } catch (DBInvalidOptionException $e) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage()
            );
        }

        // Return the information on whether the row was updated or inserted
        switch (\intval($result)) {
            case 1:
                return 'insert';
            case 2:
                return 'update';
        }

        // Row was not changed
        return '';
    }

    /**
     * @inheritDoc
     */
    public function delete(array $where): int
    {
        // We need specific WHERE restrictions, otherwise there is a huge risk
        // of overwriting to many entries
        if (\count($where) === 0) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'No restricting "where" arguments defined for DELETE'
            );
        }

        // Generate the WHERE part of the query
        $where = $this->preprocessWhere($where);

        try {
            // Execute the query
            return $this->db->delete($this->config->getTableName(), $where);
        } catch (DBInvalidOptionException $e) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage()
            );
        }
    }
}
