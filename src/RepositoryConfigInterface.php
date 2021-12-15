<?php

namespace Squirrel\Entities;

/**
 * INTERFACE: Configuration for a repository to map between table and objects
 *
 * Should only be used internally in this class - do not use in application code!
 */
interface RepositoryConfigInterface
{
    /**
     * @return string Connection name if not the default connection is used
     */
    public function getConnectionName(): string;

    /**
     * @return string Table name, optionally with database name (format: databasename.tablename)
     */
    public function getTableName(): string;

    /**
     * @return array Conversion from table to object fields
     */
    public function getTableToObjectFields(): array;

    /**
     * @return array Conversion from object to table fields
     */
    public function getObjectToTableFields(): array;

    /**
     * @return string Object class for conversion of table data to object
     */
    public function getObjectClass(): string;

    /**
     * @return array Types of the variables in the object for type casting
     */
    public function getObjectTypes(): array;

    /**
     * @return array Whether NULL is a valid type for a field
     */
    public function getObjectTypesNullable(): array;

    /**
     * @return string Autoincrement / SERIAL field if any exists for the table (otherwise an empty string)
     */
    public function getAutoincrementField(): string;
}
