<?php

namespace Squirrel\Entities;

/**
 * INTERFACE: Reading from a repository
 */
interface RepositoryReadOnlyInterface
{
    /**
     * Find entries and return them as objects
     *
     * $query can have the following contents (key-value):
     *
     * - 'where':  WHERE restrictions
     * - 'order':  ORDER BY definitions
     * - 'fields': field names of the object to fetch and populate, if not all of the data is needed
     * - 'limit':  how many results to get
     * - 'offset': at what record number to start
     * - 'lock':   if to lock selected entries (SELECT ... FOR UPDATE) for transaction (default is false)
     *
     * @param array $query Query parts as an array
     * @return object[] A list of entity objects
     */
    public function select(array $query): array;

    /**
     * Find one entry and return it as an object
     *
     * $query can have the same values as with select function, except 'limit' is set to 1
     *
     * @param array $query Query parts as an array
     * @return object|null An entity object or null if no entry was found
     */
    public function selectOne(array $query);

    /**
     * Find entries and return them as flattened fields (no field names, no entries, just the values)
     *
     * $query can have the following contents (key-value):
     *
     * - 'where':  WHERE restrictions
     * - 'order':  ORDER BY definitions
     * - 'fields': field names of the object to fetch and populate, if not all of the data is needed
     * - 'limit':  how many results to get
     * - 'offset': at what record number to start
     * - 'lock':   if to lock selected entries (SELECT ... FOR UPDATE) for transaction (default is false)
     *
     * @param array $query Query parts as an array
     * @return array An array of values of the table columns
     */
    public function selectFlattenedFields(array $query): array;

    /**
     * Count number of entries for specific $where restrictions
     *
     * $query can have the following contents (key-value):
     *
     * - 'where':  WHERE restrictions
     * - 'lock':   if to lock selected entries (SELECT ... FOR UPDATE) for transaction (default is false)
     *
     * @param array $query Query parts as an array
     * @return int Number of entries
     */
    public function count(array $query): int;
}
