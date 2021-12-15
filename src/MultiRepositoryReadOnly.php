<?php

namespace Squirrel\Entities;

use Squirrel\Debug\Debug;
use Squirrel\Queries\Builder\BuilderInterface;
use Squirrel\Queries\DBException;
use Squirrel\Queries\DBInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * If more than one table needs to be selected or updated at once this class
 * combines the knowledge of multiple Repository classes to create
 * a query which is simple and secure
 */
class MultiRepositoryReadOnly implements MultiRepositoryReadOnlyInterface
{
    protected DBInterface $db;

    public function count(array $query): int
    {
        $sanitizedQuery = [
            'fields' => [
                'num' => 'COUNT(*)',
            ],
        ];

        $sanitizedQuery['repositories'] = $query['repositories'];

        if (isset($query['tables'])) {
            $sanitizedQuery['tables'] = $query['tables'];
        }

        if (isset($query['where'])) {
            $sanitizedQuery['where'] = $query['where'];
        }

        if (isset($query['lock'])) {
            $sanitizedQuery['lock'] = $query['lock'];
        }

        // Use our internal functions to not repeat ourselves
        $result = $this->fetchOne($sanitizedQuery);

        return $result['num'] ?? 0;
    }

    public function select(array $query): MultiRepositorySelectQueryInterface
    {
        // Freeform query was detected
        if (isset($query['query']) || isset($query['parameters'])) {
            [$sqlQuery, $parameters, $selectTypes, $selectTypesNullable] = $this->buildSelectQueryFreeform($query);
        } else { // Structured query
            [$sqlQuery, $selectTypes, $selectTypesNullable] = $this->buildSelectQueryStructured($query);
        }

        // Get all the data from the database
        try {
            return new MultiRepositorySelectQuery(
                $this->db->select($sqlQuery, $parameters ?? []),
                $selectTypes,
                $selectTypesNullable,
            );
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                $e->getMessage(),
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                previousException: $e->getPrevious(),
            );
        }
    }

    public function fetch(MultiRepositorySelectQueryInterface $selectQuery): ?array
    {
        try {
            $result = $this->db->fetch($selectQuery->getQuery());
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                $e->getMessage(),
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                previousException: $e->getPrevious(),
            );
        }

        if ($result === null) {
            return null;
        }

        return $this->processSelectResult($result, $selectQuery->getTypes(), $selectQuery->getTypesNullable());
    }

    public function clear(MultiRepositorySelectQueryInterface $selectQuery): void
    {
        try {
            $this->db->clear($selectQuery->getQuery());
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                $e->getMessage(),
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                previousException: $e->getPrevious(),
            );
        }
    }

    public function fetchOne(array $query): ?array
    {
        if (isset($query['limit']) && $query['limit'] !== 1) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                'Row limit cannot be set for fetchOne query: ' . Debug::sanitizeData($query),
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
            );
        }

        // Use our internal functions to not repeat ourselves
        $selectQuery = $this->select($query);
        $result = $this->fetch($selectQuery);
        $this->clear($selectQuery);

        // Return the result object
        return $result;
    }

    public function fetchAll(array $query): array
    {
        // Freeform query was detected
        if (isset($query['query']) || isset($query['parameters'])) {
            [$sqlQuery, $parameters, $selectTypes, $selectTypesNullable] = $this->buildSelectQueryFreeform($query);
        } else { // Structured query
            [$sqlQuery, $selectTypes, $selectTypesNullable] = $this->buildSelectQueryStructured($query);
        }

        // Get all the data from the database
        try {
            $tableResults = $this->db->fetchAll($sqlQuery, $parameters ?? []);
        } catch (DBException $e) {
            throw Debug::createException(
                \get_class($e),
                $e->getMessage(),
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                previousException: $e->getPrevious(),
            );
        }

        // Process the select results
        return $this->processSelectResults($tableResults, $selectTypes, $selectTypesNullable);
    }

    public function fetchAllAndFlatten(array $query): array
    {
        $processedResults = $this->fetchAll($query);

        $list = [];

        // Go through table results
        foreach ($processedResults as $objIndex => $objEntry) {
            // Go through all table fields
            foreach ($objEntry as $fieldName => $fieldValue) {
                $list[] = $fieldValue;
            }
        }

        return $list;
    }

    /**
     * Process options and make sure all values are valid
     *
     * @param array $validOptions List of valid options and default values for them
     * @param array $options List of provided options which need to be processed
     * @param bool $writing Whether this is a writing operation or not
     */
    protected function processOptions(array $validOptions, array $options, bool $writing = false): array
    {
        // Reset DB class - needs to be set by the current options
        $dbInstance = null;

        // Copy over the default valid options as a starting point for our options
        $sanitizedOptions = $validOptions;

        // Go through the defined options
        foreach ($options as $optKey => $optVal) {
            // Defined option is not in the list of valid options
            if (!isset($validOptions[$optKey])) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Unknown option key ' . Debug::sanitizeData($optKey),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // Make sure the variable type for the defined option is valid
            switch ($optKey) {
                // These are checked & converted by SQL component
                case 'limit':
                case 'offset':
                case 'lock':
                    break;
                // Already type hinted "query" as string
                case 'query':
                    break;
                default:
                    if (!\is_array($optVal)) {
                        throw Debug::createException(
                            DBInvalidOptionException::class,
                            'Option key ' . Debug::sanitizeData($optKey) .
                            ' had a non-array value: ' . Debug::sanitizeData($optVal),
                            ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                        );
                    }
                    break;
            }

            $sanitizedOptions[$optKey] = $optVal;
        }

        // Make sure tables array was defined
        if (!isset($sanitizedOptions['repositories']) || \count($sanitizedOptions['repositories']) === 0) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                'No repositories specified',
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
            );
        }

        // No table joins defined - just join them by "default" via repositories definition
        if (isset($validOptions['tables']) && \count($sanitizedOptions['tables']) === 0) {
            $sanitizedOptions['tables'] = \array_keys($sanitizedOptions['repositories']);
        }

        // WHERE needs some restrictions to glue the tables together - except if there is only one repository
        if (
            isset($validOptions['where'])
            && \count($sanitizedOptions['where']) === 0
            && \count($sanitizedOptions['repositories']) > 1
        ) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                'No "where" definitions',
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
            );
        }

        // SELECT fields need to be defined
        if (isset($validOptions['fields']) && \count($sanitizedOptions['fields']) === 0) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                'No "fields" definition',
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
            );
        }

        // Query in freeform selects and updates needs to not be empty
        if (isset($validOptions['query']) && \strlen($sanitizedOptions['query']) === 0) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                'No "query" definition',
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
            );
        }

        // Make sure parameters for a freestyle query are valid
        if (isset($validOptions['parameters']) && \count($sanitizedOptions['parameters']) > 0) {
            // Remove keys from parameters - they are not needed
            $sanitizedOptions['parameters'] = \array_values($sanitizedOptions['parameters']);

            // Check all provided parameters
            foreach ($sanitizedOptions['parameters'] as $key => $value) {
                // Only scalar values are allowed
                if (!\is_scalar($value)) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Non-scalar "parameters" definition',
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }

                // Convert bool to int
                if (\is_bool($value)) {
                    $value = \intval($value);
                }

                $sanitizedOptions['parameters'][$key] = $value;
            }
        }

        /**
         * Name of the tables for this query
         *
         * @var array
         */
        $tableName = [];

        /**
         * Conversion from object to table fields
         *
         * @var array
         */
        $objectToTableFields = [];

        /**
         * Types of the variables in the object for type casting
         *
         * @var array
         */
        $objectTypes = [];

        /**
         * Whether variables can be NULL or not
         *
         * @var array
         */
        $objectTypesNullable = [];

        // Go through tables to prepare the repositories
        foreach ($sanitizedOptions['repositories'] as $name => $class) {
            // Make sure every entry in the tables array is valid
            if (!\is_string($name) || \strpos($name, '.') !== false) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid "repositories" key definition: ' . Debug::sanitizeData($name),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            } elseif ($class instanceof RepositoryBuilderReadOnlyInterface) {
                // Make sure the repository is writeable if we are doing a writing query
                if ($writing === true && !($class instanceof RepositoryBuilderWriteableInterface)) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Non-writeable "repositories" object definition for writing operation',
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }

                try {
                    // Dive into the repository builder class and get the raw repository behind it
                    $builderRepositoryReflection = new \ReflectionClass($class);
                    $builderRepositoryPropertyReflection = $builderRepositoryReflection->getProperty('repository');
                    $builderRepositoryPropertyReflection->setAccessible(true);
                    $baseRepository = $builderRepositoryPropertyReflection->getValue($class);

                    // Get configuration from within the base repository
                    $baseRepositoryReflection = new \ReflectionClass($baseRepository);
                    $baseRepositoryPropertyReflection = $baseRepositoryReflection->getProperty('config');
                    $baseRepositoryPropertyReflection->setAccessible(true);
                    $class = $baseRepositoryPropertyReflection->getValue($baseRepository);

                    // Get DBInterface from base repository
                    $baseRepositoryPropertyReflection = $baseRepositoryReflection->getProperty('db');
                    $baseRepositoryPropertyReflection->setAccessible(true);
                    $dbClass = $baseRepositoryPropertyReflection->getValue($baseRepository);

                    // Make sure all DBInterface instances are the same = the same connection is used
                    if (isset($dbInstance) && $dbClass !== $dbInstance) {
                        throw Debug::createException(
                            DBInvalidOptionException::class,
                            'Repositories have different database connections, combined query is not possible',
                            ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                        );
                    }

                    $dbInstance = $dbClass;
                } catch (\ReflectionException $e) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Repository configuration could not be retrieved through reflection, ' .
                        'repository class not as expected: ' . $e->getMessage(),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }
            } elseif ($class instanceof RepositoryReadOnlyInterface) {
                // Make sure the repository is writeable if we are doing a writing query
                if ($writing === true && !($class instanceof RepositoryWriteableInterface)) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Non-writeable "repositories" object definition for writing operation',
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }

                try {
                    $baseRepositoryReflection = new \ReflectionClass($class);

                    // Get DBInterface from base repository
                    $baseRepositoryPropertyReflection = $baseRepositoryReflection->getProperty('db');
                    $baseRepositoryPropertyReflection->setAccessible(true);
                    $dbClass = $baseRepositoryPropertyReflection->getValue($class);

                    // Get configuration from within the base repository
                    $baseRepositoryPropertyReflection = $baseRepositoryReflection->getProperty('config');
                    $baseRepositoryPropertyReflection->setAccessible(true);
                    $class = $baseRepositoryPropertyReflection->getValue($class);

                    // Make sure all DBInterface instances are the same = the same connection is used
                    if (isset($dbInstance) && $dbClass !== $dbInstance) {
                        throw Debug::createException(
                            DBInvalidOptionException::class,
                            'Repositories have different database connections, combined query is not possible',
                            ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                        );
                    }

                    $dbInstance = $dbClass;
                } catch (\ReflectionException $e) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Repository configuration could not be retrieved through reflection, ' .
                        'repository class not as expected: ' . $e->getMessage(),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }
            } else {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid repository specified, does not implement ' .
                    'RepositoryReadOnlyInterface or RepositoryBuilderReadOnlyInterface: ' .
                    Debug::sanitizeData($sanitizedOptions['repositories']),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // Name of the table
            $tableName[$name] = $class->getTableName();

            // Conversion from object to table fields
            $objectToTableFields[$name] = $class->getObjectToTableFields();

            // Types of the variables in the object for type casting
            $objectTypes[$name] = $class->getObjectTypes();

            // If a variable can be NULL or not
            $objectTypesNullable[$name] = $class->getObjectTypesNullable();
        }

        if ($dbInstance instanceof DBInterface) {
            $this->db = $dbInstance;
        } else {
            throw Debug::createException(
                DBInvalidOptionException::class,
                'Repositories did not contain a valid database connection' .
                Debug::sanitizeData($sanitizedOptions['repositories']),
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
            );
        }

        // Remove repositories data - not needed for query to DBInterface
        unset($sanitizedOptions['repositories']);

        // Return all processed options and object-to-table information
        return [$sanitizedOptions, $tableName, $objectToTableFields, $objectTypes, $objectTypesNullable];
    }

    private function buildSelectQueryStructured(array $query): array
    {
        // Process options and make sure all values are valid
        [
            $sanitizedOptions,
            $tableName,
            $objectToTableFields,
            $objectTypes,
            $objectTypesNullable,
        ] = $this->processOptions([
            'repositories' => [],
            'fields' => [],
            'tables' => [],
            'where' => [],
            'group' => [],
            'order' => [],
            'limit' => 0,
            'offset' => 0,
            'lock' => false,
        ], $query);

        // Build SELECT part of the query
        [$sanitizedOptions['fields'], $selectTypes, $selectTypesNullable] = $this->buildFieldSelection(
            $sanitizedOptions['fields'],
            $objectToTableFields,
            $objectTypes,
            $objectTypesNullable,
        );

        // List of finished FROM expressions, to be imploded with , + possible query values
        $sanitizedOptions['tables'] = $this->preprocessJoins(
            $sanitizedOptions['tables'],
            $tableName,
            $objectToTableFields,
        );

        // List of finished WHERE expressions, to be imploded with ANDs
        $sanitizedOptions['where'] = $this->preprocessWhere($sanitizedOptions['where'], $objectToTableFields);

        // GROUP BY was defined
        if (isset($sanitizedOptions['group']) && \count($sanitizedOptions['group']) > 0) {
            $sanitizedOptions['group'] = $this->preprocessGroup($sanitizedOptions['group'], $objectToTableFields);
        } else {
            unset($sanitizedOptions['group']);
        }

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

        // No offset - remove it from options
        if ($sanitizedOptions['offset'] === 0) {
            unset($sanitizedOptions['offset']);
        }

        // No lock - remove it from options
        if ($sanitizedOptions['lock'] === false) {
            unset($sanitizedOptions['lock']);
        }

        return [$sanitizedOptions, $selectTypes, $selectTypesNullable];
    }

    private function buildSelectQueryFreeform(array $options): array
    {
        // Process options and make sure all values are valid
        [
            $sanitizedOptions,
            $tableName,
            $objectToTableFields,
            $objectTypes,
            $objectTypesNullable,
        ] = $this->processOptions([
            'repositories' => [],
            'fields' => [],
            'query' => '',
            'parameters' => [],
        ], $options);

        // Process the query
        $sqlQuery = $this->buildFreeform($sanitizedOptions['query'], $tableName, $objectToTableFields);

        // Build select part of the query
        [$selectProcessed, $selectTypes, $selectTypesNullable] = $this->buildFieldSelection(
            $sanitizedOptions['fields'],
            $objectToTableFields,
            $objectTypes,
            $objectTypesNullable,
            true,
        );

        return [
            'SELECT ' . \implode(',', $selectProcessed) . ' FROM ' . $sqlQuery,
            $sanitizedOptions['parameters'],
            $selectTypes,
            $selectTypesNullable,
        ];
    }

    /**
     * Build freeform query by replacing object names and object field names with the
     * actual table names and table field names
     */
    protected function buildFreeform(string $query, array $tableName, array $objectToTableFields): string
    {
        // Replace all expressions of all involved repositories
        foreach ($objectToTableFields as $table => $tableFields) {
            // Replace table name placeholders
            $query = \str_replace(
                ':' . $table . ':',
                $this->db->quoteIdentifier($tableName[$table]) . ' ' . $this->db->quoteIdentifier($table),
                $query,
                $count,
            );

            // Replace all table fields with correct values
            foreach ($tableFields as $objFieldName => $sqlFieldName) {
                $query = \str_replace(
                    ':' . $table . '.' . $objFieldName . ':',
                    $this->db->quoteIdentifier($table . '.' . $sqlFieldName),
                    $query,
                    $count,
                );
            }
        }

        // If we still have unresolved expressions, something went wrong
        if (\strpos($query, ':') !== false) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                'Invalid "query" definition, unresolved colons remain',
                ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
            );
        }

        // Return processed SQL query
        return $query;
    }

    /**
     * Build SELECT part of the query
     */
    private function buildFieldSelection(
        array $selectOptions,
        array $objectToTableFields,
        array $objectTypes,
        array $objectTypesNullable,
        bool $generateSql = false,
    ): array {
        // Calculated select fields
        $selectProcessed = [];
        $selectTypes = [];
        $selectTypesNullable = [];

        // Go through all the select fields
        foreach ($selectOptions as $name => $field) {
            // No custom name for the field
            if (\is_int($name)) {
                $name = $field;
            }

            // Name always has to be a string
            if (!\is_string($name)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid "fields" definition, key is not a string: ' . Debug::sanitizeData($name),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // Field always has to be a string
            if (!\is_string($field)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid "fields" definition, value for ' .
                    Debug::sanitizeData($name) . ' is not a string: ' . Debug::sanitizeData($field),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // No expressions allowed in name part!
            if (\strpos($name, ':') !== false) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid "fields" definition, name ' .
                    Debug::sanitizeData($name) . ' contains a colon',
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // Special case of COUNT(*) - unlike any other SQL expression, and it should work
            if (\strtoupper($field) === 'COUNT(*)') {
                $selectProcessed[] = $field . ' AS ' . '"' . $name . '"';
                $selectTypes[$name] = 'int';
            } elseif (\strpos($field, ':') === false) { // No expression in field part
                // Get separated table and field parts
                $fieldParts = \explode('.', $field);

                // Field does not exist in this way
                if (!isset($fieldParts[1]) || !isset($objectToTableFields[$fieldParts[0]][$fieldParts[1]])) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Invalid "fields" definition, unknown field name: ' .
                        Debug::sanitizeData($field),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }

                // We map the SQL field to the full object field (table.field)
                if ($generateSql === true) {
                    $selectProcessed[] = $this->db->quoteIdentifier(
                        $fieldParts[0] . '.' . $objectToTableFields[$fieldParts[0]][$fieldParts[1]],
                    ) . ' AS "' . $name . '"';
                } else {
                    $selectProcessed[$name] = $fieldParts[0] . '.' .
                        $objectToTableFields[$fieldParts[0]][$fieldParts[1]];
                }
                $selectTypes[$name] = $objectTypes[$fieldParts[0]][$fieldParts[1]];
                $selectTypesNullable[$name] = $objectTypesNullable[$fieldParts[0]][$fieldParts[1]];
            } else { // Expressions in field part
                // The type guessed by the used table fields
                $type = '';
                $nullable = false;

                // Replace all expressions of all involved repositories
                foreach ($objectToTableFields as $table => $tableFields) {
                    foreach ($tableFields as $objFieldName => $sqlFieldName) {
                        $field = \str_replace(
                            ':' . $table . '.' . $objFieldName . ':',
                            $this->db->quoteIdentifier($table . '.' . $sqlFieldName),
                            $field,
                            $count,
                        );

                        // Replacement occured, so this field name is used
                        if ($count > 0) {
                            // We narrow the type to bool if only bool values are used
                            if ($objectTypes[$table][$objFieldName] === 'bool' && $type === '') {
                                $type = 'bool';
                            } elseif (
                                $objectTypes[$table][$objFieldName] === 'int' &&
                                ($type === '' || $type === 'bool')
                            ) { // We narrow the type to int if only int and bool values are used
                                $type = 'int';
                            } elseif ($objectTypes[$table][$objFieldName] === 'float' && $type !== 'string') {
                                // If any float values are used, we use float type if there are no strings
                                $type = 'float';
                            } elseif ($objectTypes[$table][$objFieldName] === 'string') {
                                // As soon as a string type is used we always use string type
                                $type = 'string';
                            }

                            // NULL is a possible value for this field
                            if ($objectTypesNullable[$table][$objFieldName] === true) {
                                $nullable = true;
                            }
                        }
                    }
                }

                // If we still have unresolved expressions, something went wrong
                if (\strpos($field, ':') !== false) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Invalid "fields" definition, unresolved colons: ' .
                        Debug::sanitizeData($field),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }

                // We guess the type is string if we have a CONCAT or REPLACE in the string
                if (\strpos($field, 'CONCAT') !== false || \strpos($field, 'REPLACE') !== false) {
                    $type = 'string';
                }

                // Assign the select expression
                $selectProcessed[] = '(' . $field . ')' . ' AS ' . '"' . $name . '"';
                $selectTypes[$name] = $type;
                $selectTypesNullable[$name] = $nullable;
            }
        }

        return [$selectProcessed, $selectTypes, $selectTypesNullable];
    }

    /**
     * Process the results retrieved from a SELECT query
     */
    private function processSelectResults(
        array $tableObjects,
        array $selectTypes,
        array $selectTypesNullable,
    ): array {
        // Go through result set
        foreach ($tableObjects as $entryCount => $entry) {
            $tableObjects[$entryCount] = $this->processSelectResult($entry, $selectTypes, $selectTypesNullable);
        }

        return $tableObjects;
    }

    private function processSelectResult(
        array $entry,
        array $selectTypes,
        array $selectTypesNullable,
    ): array {
        foreach ($entry as $key => $value) {
            // Special case of nullable types
            if (\is_null($value) && $selectTypesNullable[$key] === true) {
                $entry[$key] = null;
                continue;
            }

            switch ($selectTypes[$key]) {
                case 'int':
                    $entry[$key] = \intval($value);
                    break;
                case 'bool':
                    $entry[$key] = \boolval($value);
                    break;
                case 'float':
                    $entry[$key] = \floatval($value);
                    break;
                case 'string':
                    $entry[$key] = \strval($value);
                    break;
                default:
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Unknown casting for object variable ' . Debug::sanitizeData($key),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
            }
        }

        return $entry;
    }

    /**
     * Prepare the joins between tables part for the SQL component
     */
    protected function preprocessJoins(array $tables, array $tableNames, array $objectToTableFields): array
    {
        // List of table selection, needs to be imploded with a comma for SQL query
        $tablesProcessed = [];

        // Go through table selection
        foreach ($tables as $expression => $values) {
            // No values, only an expression
            if (\is_int($expression)) {
                $expression = $values;
                $values = null;
            }

            // Expression always has to be a string
            if (!\is_string($expression)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid "tables" / table join definition, expression is not a string: ' .
                    Debug::sanitizeData($expression),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // No expression, only a table name
            if (\strpos($expression, ':') === false) {
                // Make sure the table alias exists
                if (!isset($tableNames[$expression])) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Invalid "tables" / table join definition, alias not found: ' .
                        Debug::sanitizeData($expression),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }

                // Quoting not necessary, will be handled by SQL component
                $tablesProcessed[] = $tableNames[$expression] . ' ' . $expression;
            } else { // An expression with : variables
                // Replace all expressions of all involved repositories
                foreach ($objectToTableFields as $table => $tableFields) {
                    foreach ($tableFields as $objFieldName => $sqlFieldName) {
                        $expression = \str_replace(
                            ':' . $table . '.' . $objFieldName . ':',
                            $this->db->quoteIdentifier($table . '.' . $sqlFieldName),
                            $expression,
                            $count,
                        );
                    }
                }

                // Replace all table names and insert the aliases
                foreach ($tableNames as $tableNameAlias => $tableNameReal) {
                    $expression = \str_replace(
                        ':' . $tableNameAlias . ':',
                        $this->db->quoteIdentifier($tableNameReal) . ' ' . $this->db->quoteIdentifier($tableNameAlias),
                        $expression,
                    );
                }

                // If we still have unresolved expressions, something went wrong
                if (\strpos($expression, ':') !== false) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Invalid "tables" / table join definition, ' .
                        'unconverted objects/table names found in expression: ' .
                        Debug::sanitizeData($expression),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }

                // Add expression to from tables
                if ($values === null) {
                    $tablesProcessed[] = $expression;
                } else {
                    $tablesProcessed[$expression] = $values;
                }
            }
        }

        return $tablesProcessed;
    }

    /**
     * Prepare the WHERE clauses for SQL component
     */
    protected function preprocessWhere(array $whereOptions, array $objectToTableFields): array
    {
        // List of finished WHERE expressions, to be imploded with ANDs
        $whereProcessed = [];

        // Go through table selection
        foreach ($whereOptions as $expression => $values) {
            // Switch around expression and values if there are no values
            if (\is_int($expression)) {
                $expression = $values;
                $values = null;
            }

            // Expression always has to be a string
            if (!\is_string($expression)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid "where" definition, expression is not a string: ' .
                    Debug::sanitizeData($expression),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // No expression, only a table field name
            if (\strpos($expression, ':') === false) {
                // Values have to be defined for us to make a predefined equals query
                if (isset($values)) {
                    // Get separated table and field parts
                    $fieldParts = \explode('.', $expression);

                    // Field was not found
                    if (!isset($fieldParts[1]) || !isset($objectToTableFields[$fieldParts[0]][$fieldParts[1]])) {
                        throw Debug::createException(
                            DBInvalidOptionException::class,
                            'Invalid "where" definition, field name not found: ' .
                            Debug::sanitizeData($expression),
                            ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                        );
                    }

                    // Convert field name
                    $expression = $fieldParts[0] . '.' . $objectToTableFields[$fieldParts[0]][$fieldParts[1]];
                }
            } else { // Freestyle expression
                // Replace all expressions of all involved repositories
                foreach ($objectToTableFields as $table => $tableFields) {
                    foreach ($tableFields as $objFieldName => $sqlFieldName) {
                        $expression = \str_replace(
                            ':' . $table . '.' . $objFieldName . ':',
                            $this->db->quoteIdentifier($table . '.' . $sqlFieldName),
                            $expression,
                            $count,
                        );
                    }
                }

                // If we still have unresolved expressions, something went wrong
                if (\strpos($expression, ':') !== false) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Invalid "where" definition, unresolved colons remain in expression: ' .
                        Debug::sanitizeData($expression),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }
            }

            // Add the where definition to the processed list
            if (isset($values)) {
                $whereProcessed[$expression] = $values;
            } else {
                $whereProcessed[] = $expression;
            }
        }

        return $whereProcessed;
    }

    /**
     * Build GROUP BY clause and add query values
     */
    private function preprocessGroup(array $groupByOptions, array $objectToTableFields): array
    {
        // List of finished WHERE expressions, to be imploded with ANDs
        $groupByProcessed = [];

        // Go through table selection
        foreach ($groupByOptions as $expression => $values) {
            // Switch around expression and values if there are no values
            if (\is_int($expression)) {
                $expression = $values;
                $values = null;
            }

            // Expression always has to be a string
            if (!\is_string($expression)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid "group" / group by definition, expression is not a string: ' .
                    Debug::sanitizeData($expression),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // No expression, only a table field name
            if (\strpos($expression, ':') === false) {
                // Get separated table and field parts
                $fieldParts = \explode('.', $expression);

                // Field was not found
                if (!isset($objectToTableFields[$fieldParts[0]][$fieldParts[1]])) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Invalid "group" / group by definition, field name not found in any repository: ' .
                        Debug::sanitizeData($expression) . ' within ' . Debug::sanitizeData($groupByOptions),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }
            } else { // Freestyle expression - not allowed
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid "group" / group by definition, no variables are allowed in expression: ' .
                    Debug::sanitizeData($expression),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // Add to list of finished expressions
            $groupByProcessed[] = $fieldParts[0] . '.' . $objectToTableFields[$fieldParts[0]][$fieldParts[1]];
        }

        return $groupByProcessed;
    }

    /**
     * Prepare the ORDER BY clauses for SQL component
     */
    protected function preprocessOrder(array $orderOptions, array $objectToTableFields): array
    {
        // List of finished WHERE expressions, to be imploded with ANDs
        $orderProcessed = [];

        // Go through table selection
        foreach ($orderOptions as $expression => $direction) {
            // If there is no explicit order we set it to ASC
            if (\is_int($expression)) {
                $expression = $direction;
                $direction = null;
            }

            // Expression always has to be a string
            if (!\is_string($expression)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    'Invalid "order" / order by definition, expression is not a string: ' .
                    Debug::sanitizeData($expression),
                    ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                );
            }

            // No expression, only a table field name
            if (\strpos($expression, ':') === false) {
                // Get separated table and field parts
                $fieldParts = \explode('.', $expression);

                // Field was found - convert it
                if (count($fieldParts) === 2 && isset($objectToTableFields[$fieldParts[0]][$fieldParts[1]])) {
                    $expression = $fieldParts[0] . '.' . $objectToTableFields[$fieldParts[0]][$fieldParts[1]];
                }
            } else { // Freestyle expression
                // Replace all field names with the sql field name and escape characters around it
                foreach ($objectToTableFields as $table => $tableFields) {
                    foreach ($tableFields as $objFieldName => $sqlFieldName) {
                        $expression = \str_replace(
                            ':' . $table . '.' . $objFieldName . ':',
                            chr(27) . $table . '.' . $sqlFieldName . chr(27),
                            $expression,
                            $count,
                        );
                    }
                }

                // If we still have unresolved expressions, something went wrong
                if (\strpos($expression, ':') !== false) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        'Invalid "order" / order by definition, unconverted object names found in expression: ' .
                        Debug::sanitizeData($expression),
                        ignoreClasses: [MultiRepositoryReadOnlyInterface::class, BuilderInterface::class],
                    );
                }

                // Replace the escape markers back to colons
                $expression = str_replace(chr(27), ':', $expression);
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
}
