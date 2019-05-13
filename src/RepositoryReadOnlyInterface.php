<?php

namespace Squirrel\Entities;

/**
 * INTERFACE: Reading from a repository
 */
interface RepositoryReadOnlyInterface
{
    /**
     * Count number of entries for specific $where restrictions
     *
     * $query can have the following contents (key-value):
     *
     * - 'where':  WHERE restrictions
     * - 'lock':   if to lock selected entries (SELECT ... FOR UPDATE) for transaction (default is false)
     *
     * @param array $query Query parts as an array
     * @psalm-param array{where?:array,lock?:bool} $query
     * @return int Number of entries
     */
    public function count(array $query): int;

    /**
     * Find entries and returns the select query that can be passed to fetch and clear
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
     * @psalm-param array{fields?:array,field?:string,where?:array,order?:array,limit?:int,offset?:int,lock?:bool} $query
     * @return RepositorySelectQueryInterface Reference to the underlying select query
     */
    public function select(array $query): RepositorySelectQueryInterface;

    /**
     * Find one entry from a result set and return it as an object
     *
     * @param RepositorySelectQueryInterface $selectQuery
     * @return object|null One entity object, or an array of flattened fields
     */
    public function fetch(RepositorySelectQueryInterface $selectQuery);

    /**
     * Clear existing result set
     *
     * @param RepositorySelectQueryInterface $selectQuery
     * @return void
     */
    public function clear(RepositorySelectQueryInterface $selectQuery): void;

    /**
     * Find one entry and return it as an object
     *
     * $query can have the same values as with select function, except 'limit' is set to 1
     *
     * @param array $query Query parts as an array
     * @psalm-param array{fields?:array,field?:string,where?:array,order?:array,lock?:bool} $query
     * @return object|null An entity object or null if no entry was found
     */
    public function fetchOne(array $query);

    /**
     * Find all entries and return them as objects
     *
     * $query can have the same values as with select function, with the addition of:
     *
     * 'flattenFields' (set to true or false, default is false):
     *
     * Return results as flattened fields (no field names, no entries, just an array with
     * the values), examples where this might be useful:
     *
     * - you want a list of unique ids and don't care about any other fields
     * - you want a list of names or words, for example all cities a user used in his
     *   orders, or all email addresses associated with a user
     *
     * The flattened results can be run through array_unique to remove duplicates, if
     * necessary - fetchAll does not that do that for you
     *
     * @param array $query Query parts as an array
     * @psalm-param array{fields?:array,field?:string,where?:array,order?:array,limit?:int,offset?:int,lock?:bool} $query
     * @return array A list of entity objects, or a list of flattened values
     */
    public function fetchAll(array $query);
}
