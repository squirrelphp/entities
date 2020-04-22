<?php

namespace Squirrel\Entities;

use Squirrel\Debug\Debug;
use Squirrel\Entities\Action\ActionInterface;
use Squirrel\Queries\DBException;
use Squirrel\Queries\DBInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;
use Squirrel\Queries\LargeObject;

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
     * Conversion from table results to object
     *
     * @var array
     */
    protected $tableToObjectFields = [];

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
     * @var \ReflectionClass<object>|null
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
        $this->tableToObjectFields = $config->getTableToObjectFields();
        $this->objectToTableFields = $config->getObjectToTableFields();
        $this->objectTypes = $config->getObjectTypes();
        $this->objectTypesNullable = $config->getObjectTypesNullable();
    }

    /**
     * @inheritDoc
     */
    public function count(array $query): int
    {
        // Basic query counting the rows
        $sanitizedQuery = [
            'table' => $this->config->getTableName(),
            'fields' => [
                'num' => 'COUNT(*)',
            ],
        ];

        // Make sure lock is valid and only added if set to true
        if ($this->booleanSettingValidation($query['lock'] ?? false, 'lock') === true) {
            $sanitizedQuery['lock'] = true;
        }

        // Add WHERE restrictions
        if (isset($query['where']) && \count($query['where']) > 0) {
            $sanitizedQuery['where'] = $this->preprocessWhere($query['where']);
        }

        try {
            $count = $this->db->fetchOne($sanitizedQuery);
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage(),
                $e->getPrevious()
            );
        }

        // Return count as int
        return \intval($count['num'] ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function select(array $query): RepositorySelectQueryInterface
    {
        // Process options and make sure all values are valid
        $sanitizedQuery = $this->prepareSelectQueryForLowerLayer($this->validateQueryOptions([
            'where' => [],
            'order' => [],
            'limit' => 0,
            'offset' => 0,
            'fields' => [],
            'lock' => false,
        ], $query));

        try {
            return new RepositorySelectQuery($this->db->select($sanitizedQuery), $this->config);
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage(),
                $e->getPrevious()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function fetch(RepositorySelectQueryInterface $selectQuery)
    {
        // Make sure the same repository configuration is used
        $this->compareRepositoryConfigMustBeEqual($selectQuery->getConfig());

        try {
            $result = $this->db->fetch($selectQuery->getQuery());
            return ( $result === null ? null : $this->convertResultToObject($result) );
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage(),
                $e->getPrevious()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function clear(RepositorySelectQueryInterface $selectQuery): void
    {
        // Make sure the same repository configuration is used
        $this->compareRepositoryConfigMustBeEqual($selectQuery->getConfig());

        try {
            $this->db->clear($selectQuery->getQuery());
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage(),
                $e->getPrevious()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function fetchOne(array $query)
    {
        if (isset($query['limit']) && $query['limit'] !== 1) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'Row limit cannot be set for fetchOne query: ' . Debug::sanitizeData($query)
            );
        }

        $query['limit'] = 1;

        // Use our internal functions to not repeat ourselves
        $selectQuery = $this->select($query);
        $result = $this->fetch($selectQuery);
        $this->clear($selectQuery);

        // Return the result object
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(array $query)
    {
        $flattenFields = false;

        // Whether to flatten fields and just return an array of values instead of objects
        if (isset($query['flattenFields'])) {
            $flattenFields = $this->booleanSettingValidation($query['flattenFields'], 'flattenFields');
            unset($query['flattenFields']);
        }

        // Process options and make sure all values are valid
        $sanitizedQuery = $this->prepareSelectQueryForLowerLayer($this->validateQueryOptions([
            'where' => [],
            'order' => [],
            'limit' => 0,
            'offset' => 0,
            'fields' => [],
            'lock' => false,
        ], $query));

        try {
            // Get all the data from the database
            $tableResults = $this->db->fetchAll($sanitizedQuery);
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $e->getMessage(),
                $e->getPrevious()
            );
        }

        // Special case: Flatten all field values and return an array
        if ($flattenFields === true) {
            return $this->convertResultsToFlattenedResults($tableResults);
        }

        return \array_map([$this, 'convertResultToObject'], $tableResults);
    }

    /**
     * @param array<string,mixed> $validOptions
     * @param array<string,mixed> $options
     * @return array<string,mixed>
     */
    protected function validateQueryOptions(array $validOptions, array $options): array
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
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Unknown option key ' . Debug::sanitizeData($optKey)
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
                        throw Debug::createException(
                            DBInvalidOptionException::class,
                            [RepositoryReadOnlyInterface::class, ActionInterface::class],
                            'Option key ' . Debug::sanitizeData($optKey) .
                            ' had a non-array value: ' . Debug::sanitizeData($optVal)
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
     * @param array<string,mixed> $query
     * @return array<string,mixed>
     */
    private function prepareSelectQueryForLowerLayer(array $query): array
    {
        // Set the table variable for SQL component
        $query['table'] = $this->config->getTableName();

        // Field names were restricted
        if (\count($query['fields']) > 0) {
            // Go through all provided field names
            foreach ($query['fields'] as $key => $fieldName) {
                // If we do not know a field name this is super bad
                if (!\is_string($fieldName)) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        [RepositoryReadOnlyInterface::class, ActionInterface::class],
                        'Field name is not a string: ' . Debug::sanitizeData($fieldName)
                    );
                }

                // Convert the name
                $query['fields'][$key] = $this->convertNameToTable($fieldName);
            }

            $query['fields'] = \array_values($query['fields']);
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

        return $query;
    }

    /**
     * @param mixed $shouldBeBoolean
     * @param string $settingName
     * @return bool
     */
    private function booleanSettingValidation($shouldBeBoolean, string $settingName): bool
    {
        // Make sure the setting is a boolean or at least an integer which can be clearly interpreted as boolean
        if (
            !\is_bool($shouldBeBoolean)
            && $shouldBeBoolean !== 1
            && $shouldBeBoolean !== 0
        ) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                $settingName . ' set to a non-boolean value: ' . Debug::sanitizeData($shouldBeBoolean)
            );
        }

        return \boolval($shouldBeBoolean);
    }

    private function compareRepositoryConfigMustBeEqual(RepositoryConfigInterface $config): void
    {
        if ($config != $this->config) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'Different repository used to fetch result than to do the query!'
            );
        }
    }

    /**
     * @param array<string,mixed> $tableResult
     */
    private function convertResultToObject(array $tableResult): object
    {
        // Only create reflection class once we need it, to be resource efficient
        if (!isset($this->reflectionClass)) {
            /**
             * @psalm-var class-string $objectClass
             */
            $objectClass = $this->config->getObjectClass();

            $this->reflectionClass = new \ReflectionClass($objectClass);
        }

        // Initialize object without constructor
        $useableObject = $this->reflectionClass->newInstanceWithoutConstructor();

        // Go through all table
        foreach ($tableResult as $fieldName => $fieldValue) {
            // We ignore unknown table fields
            if (!isset($this->tableToObjectFields[$fieldName])) {
                continue;
            }

            // Get object key
            $objKey = $this->tableToObjectFields[$fieldName];

            // Get reflection property, make is accessible to reflection and cache it
            if (!isset($this->reflectionProperties[$objKey])) {
                $this->reflectionProperties[$objKey] = $this->reflectionClass->getProperty($objKey);
                $this->reflectionProperties[$objKey]->setAccessible(true);
            }

            // Set the property via reflection
            $this->reflectionProperties[$objKey]
                // Set new value for our current object
                ->setValue(
                    $useableObject,
                    // Cast the new value to the correct type (string, int, float, bool)
                    $this->castObjVariable($fieldValue, $this->tableToObjectFields[$fieldName])
                );
        }

        return $useableObject;
    }

    /**
     * @param array<int,mixed> $tableResults
     * @return array<int,bool|int|float|string|null>
     */
    private function convertResultsToFlattenedResults(array $tableResults): array
    {
        $list = [];

        // Go through table results
        foreach ($tableResults as $objIndex => $tableObject) {
            // Go through all table fields
            foreach ($tableObject as $fieldName => $fieldValue) {
                $list[] = $this->castObjVariable($fieldValue, $this->tableToObjectFields[$fieldName]);
            }
        }

        return $list;
    }

    /**
     * Prepare the WHERE clauses for SQL component
     *
     * @param array<int|string,mixed> $where
     * @return array<int|string,mixed>
     *
     * @throws DBInvalidOptionException
     */
    protected function preprocessWhere(array $where): array
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
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Invalid "where" definition, expression is not a string: ' .
                    Debug::sanitizeData($whereName)
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
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        [RepositoryReadOnlyInterface::class, ActionInterface::class],
                        'Unresolved colons in "where" clause: ' .
                        Debug::sanitizeData($whereName)
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
     * @return int|float|string|LargeObject|array<int|string,mixed>|null
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
     * @param bool $isChange
     * @return int|float|string|LargeObject|null
     *
     * @throws DBInvalidOptionException
     */
    protected function castOneTableVariable($value, ?string $fieldName = null, bool $isChange = false)
    {
        // Only scalar values and null are allowed
        if (!\is_null($value) && !\is_scalar($value)) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'Invalid value for field name: ' .
                Debug::sanitizeData($fieldName) . ' => ' . Debug::sanitizeData($value)
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

            /**
             * @var int|float|string|null $value No boolean is possible because we just typecast it above
             */
            return $value;
        }

        // Make sure we know the used field name
        if (!isset($this->objectTypes[$fieldName])) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'Unknown field name: ' . Debug::sanitizeData($fieldName)
            );
        }

        // Check for null value and if it is allowed for this field name
        if (\is_null($value)) {
            // Not allowed
            if ($this->objectTypesNullable[$fieldName] !== true) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'NULL value for non-nullable field name: ' .
                    Debug::sanitizeData($fieldName)
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

        // Blob = binary large object
        if ($this->objectTypes[$fieldName] === 'blob') {
            // Large object are used for update and insert
            if ($isChange === true) {
                return new LargeObject(\strval($value));
            }

            // We let this escalate to an exception because blobs should not be used in WHERE clauses
            // or similar expressions - they are considered something to access and write, but not query,
            // except for NULL if a blob is nullable
        }

        // Always throw an exception we if hit unchartered territory
        throw Debug::createException(
            DBInvalidOptionException::class,
            [RepositoryReadOnlyInterface::class, ActionInterface::class],
            'Unknown casting for object variable: ' . Debug::sanitizeData($fieldName)
        );
    }

    /**
     * Convert :name: notations in strings from object to table notation
     *
     * @param string $expression
     * @return string
     */
    protected function convertNamesToTableInString(string $expression): string
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
    protected function convertNameToTable(string $fieldName): string
    {
        // If we do not know a field name this is super bad
        if (!isset($this->objectToTableFields[$fieldName])) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [RepositoryReadOnlyInterface::class, ActionInterface::class],
                'Unknown field name: ' . Debug::sanitizeData($fieldName)
            );
        }

        return $this->objectToTableFields[$fieldName];
    }

    /**
     * Prepare the ORDER BY clauses for SQL component
     *
     * @param array<int|string,mixed> $orderOptions
     * @return array<int|string,mixed>
     *
     * @throws DBInvalidOptionException
     */
    protected function preprocessOrder(array $orderOptions): array
    {
        // Order SQL parts
        $orderProcessed = [];

        // Go through all order contraints and apply them
        foreach ($orderOptions as $expression => $direction) {
            // Key is a number, so we need to switch fieldName and set a default direction
            if (\is_int($expression)) {
                $expression = $direction;
                $direction = null;
            }

            // Make sure we have a valid fieldname
            if (!\is_string($expression)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [RepositoryReadOnlyInterface::class, ActionInterface::class],
                    'Invalid "order" / order by definition, expression is not a string: ' .
                    Debug::sanitizeData($expression)
                );
            }

            // Wether variable was found or not
            $variableFound = (\strpos($expression, ':') !== false);

            // Expression contains not just the field name
            if (
                $variableFound === true
                || \strpos($expression, ' ') !== false
                || \strpos($expression, '(') !== false
                || \strpos($expression, ')') !== false
            ) {
                if ($variableFound === true) {
                    // Convert all :variable: values from object to table notation
                    $expression = $this->convertNamesToTableInString($expression);

                    // Variables still exist which were not resolved
                    if (\strpos($expression, ':') !== false) {
                        throw Debug::createException(
                            DBInvalidOptionException::class,
                            [RepositoryReadOnlyInterface::class, ActionInterface::class],
                            'Unresolved colons in "order" / order by clause: ' .
                            Debug::sanitizeData($expression)
                        );
                    }
                }
            } else { // Expression is just a field name
                $expression = $this->convertNameToTable($expression);
            }

            // Add order entry to processed list
            if ($direction === null) {
                $orderProcessed[] = $expression;
            } else {
                $orderProcessed[$expression] = $direction;
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
            case 'blob':
                return \strval($value);
        }

        throw Debug::createException(
            DBInvalidOptionException::class,
            [RepositoryReadOnlyInterface::class, ActionInterface::class],
            'Unknown casting for object variable: ' . Debug::sanitizeData($fieldName)
        );
    }
}
