<?php

namespace Squirrel\Entities;

/**
 * INTERFACE: Writing to and reading from a repository
 */
interface RepositoryWriteableInterface extends RepositoryReadOnlyInterface
{
    /**
     * Insert a new entry with the given fields and return newly created ID (if applicable)
     *
     * @param array<string, mixed> $fields Fields and values to use when inserting the row
     * @param bool $returnInsertId Whether there is an insert ID created which should be returned
     * @return string|null Newly inserted ID if $returnInsertId was set to true
     */
    public function insert(array $fields, bool $returnInsertId = false): ?string;

    /**
     * Insert a new entry or update the existing entry (UPSERT/MERGE)
     *
     * @param array<string, mixed> $fields Fields and values to use when inserting the row
     * @param string[] $indexFields Field names of the unique index
     * @param array|null $updateFields Updates to do if an entry already exists, defaults to $fields minus $indexFields
     */
    public function insertOrUpdate(array $fields, array $indexFields = [], ?array $updateFields = null): void;

    /**
     * Update entries with $changes restricted by $where
     *
     * @param array $changes Changes to be saved
     * @param array $where Restrictions on which entries to update
     * @return int How many entries were affected by the update
     */
    public function update(array $changes, array $where): int;

    /**
     * Remove entries according to $where restrictions
     *
     * @param array $where Restrictions on what rows to target
     * @return int Number of entries which were removed
     */
    public function delete(array $where): int;
}
