<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Action\ActionInterface;
use Squirrel\Queries\DBDebug;
use Squirrel\Queries\DBInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Repository functionality: Get data from one table and change data in that one table
 * through narrowly defined functions, leading to simple, secure and fast queries
 */
class RepositoryReadOnly implements RepositoryReadOnlyInterface
{
    /**
     * @var DBInterface
     */
    protected $db;

    /**
     * @var RepositoryConfigInterface
     */
    protected $config;

    /**
     * Conversion from object to table fields
     *
     * @var array
     */
    protected $objectToTableFields = [];

    /**
     * Types of the variables in the object for type casting
     *
     * @var array
     */
    protected $objectTypes = [];

    /**
     * Whether NULL is a valid type for a field
     *
     * @var array
     */
    protected $objectTypesNullable = [];

    /**
     * Reflection on our object class, so we can set private/protected class properties and
     * circumvent the object constructor
     *
     * @var \ReflectionClass|null
     */
    protected $reflectionClass;

    /**
     * @var \ReflectionProperty[]
     */
    protected $reflectionProperties = [];

    /**
     * @param DBInterface $db
     * @param RepositoryConfig $config
     */
    public function __construct(DBInterface $db, RepositoryConfig $config)
    {
        $this->db = $db;
        $this->config = $config;
        $this->objectToTableFields = $config->getObjectToTableFields();
        $this->objectTypes = $config->getObjectTypes();
        $this->objectTypesNullable = $config->getObjectTypesNullable();
    }

    /**
     * @inheritDoc
     */
    public function selectOne(array $query)
    {
        if (isset($query['limit']) && $query['limit'] !== 1) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'Row limit cannot be set for selectOne query: ' . DBDebug::sanitizeData($query)
            );
        }

        $query['limit'] = 1;

        // Return found objects and just return the one
        $results = $this->select($query);
        return \array_pop($results);
    }

    /**
     * @inheritDoc
     */
    public function select(array $query): array
    {
        // Process options and make sure all values are valid
        $sanitizedQuery = $this->processOptions([
            'where' => [],
            'order' => [],
            'limit' => 0,
            'offset' => 0,
            'fields' => [],
            'lock' => false,
        ], $query);

        // Return found objects
        return $this->selectQuery($sanitizedQuery);
    }

    /**
     * Process options and make sure all values are valid
     *
     * @param array $validOptions List of valid options and default values for them
     * @param array $options List of provided options which need to be processed
     * @return array
     */
    protected function processOptions(array $validOptions, array $options)
    {
        // One field shortcut - convert to fields array
        if (isset($validOptions['fields']) && isset($options['field']) && !isset($options['fields'])) {
            $options['fields'] = [$options['field']];
            unset($options['field']);
        }

        // Copy over the default valid options as a starting point for our options
        $sanitizedOptions = $validOptions;

        // Go through the defined options
        foreach ($options as $optKey => $optVal) {
            // Defined option is not in the list of valid options
            if (!isset($validOptions[$optKey])) {
                throw DBDebug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Unknown option key ' . DBDebug::sanitizeData($optKey)
                );
            }

            // Make sure the variable type for the defined option is valid
            switch ($optKey) {
                // These are checked & converted by SQL component
                case 'limit':
                case 'offset':
                case 'lock':
                    break;
                default:
                    if (!\is_array($optVal)) {
                        throw DBDebug::createException(
                            DBInvalidOptionException::class,
                            [RepositoryReadOnlyInterface::class, ActionInterface::class],
                            'Option key ' . DBDebug::sanitizeData($optKey) .
                            ' had a non-array value: ' . DBDebug::sanitizeData($optVal)
                        );
                    }
                    break;
            }

            $sanitizedOptions[$optKey] = $optVal;
        }

        // Return all processed options and object-to-table information
        return $sanitizedOptions;
    }

    /**
     * @param array $query
     * @param bool $flattenFields Whether to flatten the return field values and remove field names
     * @return array
     */
    private function selectQuery(array $query, bool $flattenFields = false): array
    {
        // Set the table variable for SQL component
        $query['table'] = $this->config->getTableName();

        // Field names were restricted
        if (\count($query['fields']) > 0) {
            $query['fields'] = $this->convertNamesToTable($query['fields']);
        } else { // Remove fields if none were defined
            unset($query['fields']);
        }

        // There are WHERE restrictions
        if (\count($query['where']) > 0) {
            $query['where'] = $this->preprocessWhere($query['where']);
        } else {
            unset($query['where']);
        }

        // Order part of the query was defined
        if (\count($query['order']) > 0) {
            $query['order'] = $this->preprocessOrder($query['order']);
        } else {
            unset($query['order']);
        }

        // No limit - remove it from options
        if ($query['limit'] === 0) {
            unset($query['limit']);
        }

        // No offset - remove it from options
        if ($query['offset'] === 0) {
            unset($query['offset']);
        }

        // No lock - remove it from options
        if ($query['lock'] === false) {
            unset($query['lock']);
        }

        try {
            // Get all the data from the database
            $tableObjects = $this->db->fetchAll($query);
        } catch (DBInvalidOptionException $e) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage()
            );
        }

        // The objects to return
        $useableObjects = [];

        // Table rows were found
        if (\is_array($tableObjects)) {
            $tableToObjectFields = $this->config->getTableToObjectFields();

            // Special case: Only one field name is retrieved. We reduce the
            // values to an array of these values
            if ($flattenFields === true) {
                $list = [];

                // Go through table results
                foreach ($tableObjects as $objIndex => $tableObject) {
                    // Go through all table fields
                    foreach ($tableObject as $fieldName => $fieldValue) {
                        $list[] = $this->castObjVariable($fieldValue, $tableToObjectFields[$fieldName]);
                    }
                }

                return $list;
            }

            // Get reflection information on the class
            if (!isset($this->reflectionClass)) {
                $this->reflectionClass = new \ReflectionClass($this->config->getObjectClass());
            }

            // Go through table results
            foreach ($tableObjects as $objIndex => $tableObject) {
                // Initialize object without constructor
                $useableObjects[$objIndex] = $this->reflectionClass->newInstanceWithoutConstructor();

                // Go through all table
                foreach ($tableObject as $fieldName => $fieldValue) {
                    // We ignore unknown table fields
                    if (!isset($tableToObjectFields[$fieldName])) {
                        continue;
                    }

                    // Get object key
                    $objKey = $tableToObjectFields[$fieldName];

                    // Get reflection property, make is accessible to reflection and cache it
                    if (!isset($this->reflectionProperties[$objKey])) {
                        $this->reflectionProperties[$objKey] = $this->reflectionClass->getProperty($objKey);
                        $this->reflectionProperties[$objKey]->setAccessible(true);
                    }

                    // Set the property via reflection
                    $this->reflectionProperties[$objKey]
                        // Set new value for our current object
                        ->setValue(
                            $useableObjects[$objIndex],
                            // Cast the new value to the correct type (string, int, float, bool)
                            $this->castObjVariable($fieldValue, $tableToObjectFields[$fieldName])
                        );
                }
            }
        }

        // Return found objects
        return $useableObjects;
    }

    /**
     * Convert field names to the table names
     *
     * @param array $fieldNames
     * @return array
     *
     * @throws DBInvalidOptionException
     */
    protected function convertNamesToTable(array $fieldNames)
    {
        // Result of converted names
        $convertedNames = [];

        // Go through all provided field names
        foreach ($fieldNames as $fieldName) {
            // If we do not know a field name this is super bad
            if (!\is_string($fieldName) || !isset($this->objectToTableFields[$fieldName])) {
                throw DBDebug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Unknown field name: ' . DBDebug::sanitizeData($fieldName)
                );
            }

            // Convert the name
            $convertedNames[] = $this->objectToTableFields[$fieldName];
        }

        // Return the converted fields
        return $convertedNames;
    }

    /**
     * Prepare the WHERE clauses for SQL component
     *
     * @param array $where
     * @return array
     *
     * @throws DBInvalidOptionException
     */
    protected function preprocessWhere(array $where)
    {
        // SQL restrictions as an array
        $whereProcessed = [];

        // Go through all where clauses
        foreach ($where as $whereName => $whereValue) {
            // Switch name and values if necessary
            if (\is_int($whereName)) {
                $whereName = $whereValue;
                $whereValue = [];
            }

            // Make sure we have a valid field name
            if (!\is_string($whereName)) {
                throw DBDebug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Invalid "where" definition, expression is not a string: ' .
                    DBDebug::sanitizeData($whereName)
                );
            }

            // Key contains a colon, meaning this is a string query part
            if (\strpos($whereName, ':') !== false) {
                // Cast variable values
                $whereValue = $this->castTableVariable($whereValue);

                // Convert all :variable values from object to table notation
                $whereName = $this->convertNamesToTableInString($whereName);

                // Variables still exist which were not resolved
                if (\strpos($whereName, ':') !== false) {
                    throw DBDebug::createException(
                        DBInvalidOptionException::class,
                        [RepositoryReadOnlyInterface::class, ActionInterface::class],
                        'Unresolved colons in "where" clause: ' .
                        DBDebug::sanitizeData($whereName)
                    );
                }
            } else { // Key is a string, meaning normal field - value entry
                // Cast variable values
                $whereValue = $this->castTableVariable($whereValue, $whereName);

                // Convert where field name
                $whereName = $this->convertNameToTable($whereName);
            }

            // Add the where definition to the processed list
            if (\is_array($whereValue) && \count($whereValue) === 0) {
                $whereProcessed[] = $whereName;
            } else {
                $whereProcessed[$whereName] = $whereValue;
            }
        }

        // Returned generated SQL and the new where values
        return $whereProcessed;
    }

    /**
     * Cast an object variable (array or scalar) to the correct type for a SQL query
     *
     * @param mixed $value
     * @param null|string $fieldName
     * @return int|float|string|array|null
     *
     * @throws DBInvalidOptionException
     */
    protected function castTableVariable($value, ?string $fieldName = null)
    {
        // Array - go through elements and cast them
        if (\is_array($value)) {
            foreach ($value as $key => $valueSub) {
                $value[$key] = $this->castOneTableVariable($valueSub, $fieldName);
            }
        } else { // Single scalar value - cast it
            $value = $this->castOneTableVariable($value, $fieldName);
        }

        return $value;
    }

    /**
     * Cast an object variable (only single scalar) to the correct type for a SQL query
     *
     * @param mixed $value
     * @param string|null $fieldName
     * @return int|float|string|null
     *
     * @throws DBInvalidOptionException
     */
    protected function castOneTableVariable($value, ?string $fieldName = null)
    {
        // Only scalar values and null are allowed
        if (!\is_null($value) && !\is_scalar($value)) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'Invalid value for field name: ' .
                DBDebug::sanitizeData($fieldName) . ' => ' . DBDebug::sanitizeData($value)
            );
        }

        // If we are "blind" to the exact field name we just make sure boolean
        // values are converted to int
        // This case only occurs with "freeform" query parts where there can be multiple variables
        // involved and we do not really know which, so we cannot help the user by type casting, the
        // user is on his own
        if (!isset($fieldName)) {
            if (\is_bool($value)) {
                $value = \intval($value);
            }

            return $value;
        }

        // Make sure we know the used field name
        if (!isset($this->objectTypes[$fieldName])) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'Unknown field name: ' . DBDebug::sanitizeData($fieldName)
            );
        }

        // Check for null value and if it is allowed for this field name
        if (\is_null($value)) {
            // Not allowed
            if ($this->objectTypesNullable[$fieldName] !== true) {
                throw DBDebug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'NULL value for non-nullable field name: ' .
                    DBDebug::sanitizeData($fieldName)
                );
            }

            return $value;
        }

        // We know the field type - only basic types allowed
        switch ($this->objectTypes[$fieldName]) {
            case 'int':
                return \intval($value);
            case 'bool':
                return \intval(\boolval($value));
            case 'float':
                return \floatval($value);
            case 'string':
                return \strval($value);
        }

        // Always throw an exception we if hit unchartered territory
        throw DBDebug::createException(
            DBInvalidOptionException::class,
            [RepositoryReadOnlyInterface::class, ActionInterface::class],
            'Unknown casting for object variable: ' . DBDebug::sanitizeData($fieldName)
        );
    }

    /**
     * Convert :name: notations in strings from object to table notation
     *
     * @param string $expression
     * @return string
     */
    protected function convertNamesToTableInString(string $expression)
    {
        // Convert all :variable: values from object to table notation
        foreach ($this->objectToTableFields as $objectName => $tableName) {
            $expression = \str_replace(':' . $objectName . ':', $this->db->quoteIdentifier($tableName), $expression);
        }

        return $expression;
    }

    /**
     * Convert field name to the table name
     *
     * @param string $fieldName
     * @return string
     *
     * @throws DBInvalidOptionException
     */
    protected function convertNameToTable(string $fieldName)
    {
        // If we do not know a field name this is super bad
        if (!isset($this->objectToTableFields[$fieldName])) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'Unknown field name: ' . DBDebug::sanitizeData($fieldName)
            );
        }

        return $this->objectToTableFields[$fieldName];
    }

    /**
     * Prepare the ORDER BY clauses for SQL component
     *
     * @param array $orderOptions
     * @return array
     *
     * @throws DBInvalidOptionException
     */
    protected function preprocessOrder(array $orderOptions)
    {
        // Order SQL parts
        $orderProcessed = [];

        // Go through all order contraints and apply them
        foreach ($orderOptions as $fieldName => $direction) {
            // Key is a number, so we need to switch fieldName and set a default direction
            if (\is_int($fieldName)) {
                $fieldName = $direction;
                $direction = null;
            }

            // Make sure we have a valid fieldname
            if (!\is_string($fieldName)) {
                throw DBDebug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Invalid "order" / order by definition, expression is not a string: ' .
                    DBDebug::sanitizeData($fieldName)
                );
            }

            // Variables were defined, so a freestyle order
            if (\strpos($fieldName, ':') !== false) {
                // Convert all :variable: values from object to table notation
                $fieldName = $this->convertNamesToTableInString($fieldName);

                // Variables still exist which were not resolved
                if (\strpos($fieldName, ':') !== false) {
                    throw DBDebug::createException(
                        DBInvalidOptionException::class,
                        [RepositoryReadOnlyInterface::class, ActionInterface::class],
                        'Unresolved colons in "order" / order by clause: ' .
                        DBDebug::sanitizeData($fieldName)
                    );
                }
            } else { // Specific field name
                $fieldName = $this->convertNameToTable($fieldName);
            }

            // Add order entry to processed list
            if ($direction === null) {
                $orderProcessed[] = $fieldName;
            } else {
                $orderProcessed[$fieldName] = $direction;
            }
        }

        return $orderProcessed;
    }

    /**
     * Cast an object variable to the correct type for use in an object
     *
     * @param mixed $value
     * @param string $fieldName
     * @return bool|int|float|string|null
     *
     * @throws DBInvalidOptionException
     */
    protected function castObjVariable($value, string $fieldName)
    {
        // Field is null and can be null according to config
        if (\is_null($value) && $this->objectTypesNullable[$fieldName] === true) {
            return $value;
        }

        switch ($this->objectTypes[$fieldName]) {
            case 'int':
                return \intval($value);
            case 'bool':
                return \boolval($value);
            case 'float':
                return \floatval($value);
            case 'string':
                return \strval($value);
        }

        throw DBDebug::createException(
            DBInvalidOptionException::class,
            [RepositoryReadOnlyInterface::class, ActionInterface::class],
            'Unknown casting for object variable: ' . DBDebug::sanitizeData($fieldName)
        );
    }

    /**
     * @inheritDoc
     */
    public function selectFlattenedFields(array $query): array
    {
        // Process options and make sure all values are valid
        $sanitizedQuery = $this->processOptions([
            'where' => [],
            'order' => [],
            'limit' => 0,
            'offset' => 0,
            'fields' => [],
            'lock' => false,
        ], $query);

        // Return found objects
        return $this->selectQuery($sanitizedQuery, true);
    }

    /**
     * @inheritDoc
     */
    public function count(array $query): int
    {
        // Basic query counting the rows
        $query = [
            'table' => $this->config->getTableName(),
            'fields' => [
                'num' => 'COUNT(*)',
            ],
            'where' => $this->preprocessWhere($query['where'] ?? []),
            'lock' => $query['lock'] ?? false,
        ];

        // Remove empty WHERE restrictions
        if (\count($query['where']) === 0) {
            unset($query['where']);
        }

        try {
            // Get the number from the database
            $count = $this->db->fetchOne($query);
        } catch (DBInvalidOptionException $e) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage()
            );
        }

        // Return count as int
        return \intval($count['num']);
    }
}
