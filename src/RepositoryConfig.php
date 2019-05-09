<?php

namespace Squirrel\Entities;

/**
 * VALUE OBJECT: Configuration for a repository to map between table and objects
 */
class RepositoryConfig implements RepositoryConfigInterface
{
    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var string Name of the table
     */
    private $tableName = '';

    /**
     * @var string Autoincrement / SERIAL column
     */
    private $autoincrementField = '';

    /**
     * Conversion from table to object fields
     *
     * @var array
     */
    private $tableToObjectFields = [];

    /**
     * Conversion from object to table fields
     *
     * @var array
     */
    private $objectToTableFields = [];

    /**
     * Object class for conversion of table data to object
     *
     * @var string
     */
    private $objectClass = '';

    /**
     * Types of the variables in the object for type casting
     *
     * @var array
     */
    private $objectTypes = [];

    /**
     * Whether NULL is a valid type for a field
     *
     * @var array
     */
    private $objectTypesNullable;

    public function __construct(
        string $connectionName,
        string $tableName,
        array $tableToObjectFields,
        array $objectToTableFields,
        string $objectClass,
        array $objectTypes,
        array $objectTypesNullable,
        string $autoincrementField = ''
    ) {
        $this->connectionName = $connectionName;
        $this->tableName = $tableName;
        $this->tableToObjectFields = $tableToObjectFields;
        $this->objectToTableFields = $objectToTableFields;
        $this->objectClass = $objectClass;
        $this->objectTypes = $objectTypes;
        $this->objectTypesNullable = $objectTypesNullable;
        $this->autoincrementField = $autoincrementField;
    }

    /**
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @inheritDoc
     */
    public function getTableToObjectFields(): array
    {
        return $this->tableToObjectFields;
    }

    /**
     * @inheritDoc
     */
    public function getObjectToTableFields(): array
    {
        return $this->objectToTableFields;
    }

    /**
     * @inheritDoc
     */
    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypes(): array
    {
        return $this->objectTypes;
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypesNullable(): array
    {
        return $this->objectTypesNullable;
    }

    /**
     * @inheritDoc
     */
    public function getAutoincrementField(): string
    {
        return $this->autoincrementField;
    }
}
