<?php

namespace Squirrel\Entities;

/**
 * VALUE OBJECT: Configuration for a repository to map between table and objects
 */
final readonly class RepositoryConfig implements RepositoryConfigInterface
{
    public function __construct(
        private string $connectionName,
        private string $tableName,
        /** Conversion from table to object fields */
        private array $tableToObjectFields,
        /** Conversion from object to table fields */
        private array $objectToTableFields,
        /** Object class for conversion of table data to object */
        private string $objectClass,
        /** Types of the variables in the object for type casting */
        private array $objectTypes,
        /** Whether NULL is a valid type for a field */
        private array $objectTypesNullable,
        private string $autoincrementField = '',
    ) {
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
