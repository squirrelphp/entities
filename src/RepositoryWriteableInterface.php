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
     * @param array $fields Fields and values to use when inserting the row
     * @param bool $returnInsertId Whether there is an insert ID created which should be returned
     * @return string|null Newly inserted ID if $returnInsertId was set to true
     */
    public function insert(array $fields, bool $returnInsertId = false): ?string;

    /**
     * Insert a new entry or update the existing entry (UPSERT/MERGE)
     *
     * @param array $fields Fields and values to use when inserting the row
     * @param array $indexFields Field names of the unique index
     * @param array $updateFields Updates to do if an entry already exists, defaults to $fields minus $indexFields
     * @return string Either "insert", "update" or "" (empty string means no change)
     */
    public function insertOrUpdate(array $fields, array $indexFields = [], array $updateFields = []): string;

    /**
     * Update existing fields $fields restricted by $where fields
     *
     * $query can have the following contents (key-value):
     *
     * - 'changes': SET clause with changes
     * - 'where':   WHERE restrictions
     * - 'order':   ORDER BY definitions
     * - 'limit':   how many results to get
     *
     * @param array $query Query parts as an array
     * @return int How many entries were affected by the update
     */
    public function update(array $query): int;

    /**
     * Remove entries according to $where restrictions
     *
     * @param array $where Restrictions on what rows to target
     * @return int Number of entries which were removed
     */
    public function delete(array $where): int;
}
