<?php

namespace Squirrel\Entities;

/**
 * VALUE OBJECT: Configuration for a repository to map between table and objects
 */
class RepositoryConfig implements RepositoryConfigInterface
{
    private string $connectionName;
    private string $tableName = '';
    /** @var string Autoincrement / SERIAL column */
    private string $autoincrementField = '';
    /** Conversion from table to object fields */
    private array $tableToObjectFields = [];
    /** Conversion from object to table fields */
    private array $objectToTableFields = [];
    /** Object class for conversion of table data to object */
    private string $objectClass = '';
    /** Types of the variables in the object for type casting */
    private array $objectTypes = [];
    /** Whether NULL is a valid type for a field */
    private array $objectTypesNullable;

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

    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getTableToObjectFields(): array
    {
        return $this->tableToObjectFields;
    }

    public function getObjectToTableFields(): array
    {
        return $this->objectToTableFields;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function getObjectTypes(): array
    {
        return $this->objectTypes;
    }

    public function getObjectTypesNullable(): array
    {
        return $this->objectTypesNullable;
    }

    public function getAutoincrementField(): string
    {
        return $this->autoincrementField;
    }
}
