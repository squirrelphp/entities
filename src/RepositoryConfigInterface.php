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
     * @return array
     */
    public function getTableToObjectFields(): array;

    /**
     * @return array
     */
    public function getObjectToTableFields(): array;

    /**
     * @return string
     */
    public function getObjectClass(): string;

    /**
     * @return array
     */
    public function getObjectTypes(): array;

    /**
     * @return array
     */
    public function getObjectTypesNullable(): array;

    /**
     * @return string
     */
    public function getAutoincrementField(): string;
}
