<?php

namespace Squirrel\Entities;

/**
 * INTERFACE: Multiple repositories should be read from (SELECT)
 */
interface MultiRepositoryReadOnlyInterface
{
    /**
     * Count number of entries. The options can be:
     *
     * - 'repositories': involved repositories as a name to RepositoryReadOnly / RepositoryBuilderReadOnlyInterface list
     * - 'tables': how tables are connected and which are selected (optional, default is all)
     * - 'where':  WHERE restrictions
     * - 'lock':   if to lock selected entries (SELECT ... FOR UPDATE) for transaction (optional)
     *
     * @param array $query
     * @psalm-param array{repositories:array,tables?:array,where:array,lock?:bool} $query
     * @return int
     */
    public function count(array $query): int;

    /**
     * Select query. The options can be:
     *
     * - 'repositories': involved repositories as a name to RepositoryReadOnly / RepositoryBuilderReadOnlyInterface list
     * - 'tables': how tables are connected and which are selected (optional, default is all)
     * - 'fields': what to select as a name to field name list
     * - 'where':  WHERE restrictions
     * - 'group':  GROUP BY definitions (optional)
     * - 'order':  ORDER BY definitions (optional)
     * - 'limit':  how many results to get (optional)
     * - 'offset': at what record number to start (optional)
     * - 'lock':   if to lock selected entries (SELECT ... FOR UPDATE) for transaction (optional)
     *
     * A special possibility is a freeform query, with the following options:
     *
     * - 'repositories': involved repositories as a name to RepositoryConfigInterface list
     * - 'fields': what to select as a name to field name list
     * - 'query':  Query as a string
     * - 'parameters': Array of query value parameters
     * - 'lock':   if to lock selected entries (SELECT ... FOR UPDATE) for transaction (optional)
     *
     * Freeform queries should almost never be necessary and is considered bad practice, yet it is still a possibility
     * because some queries (for example with subqueries or other complicated parts) cannot be structured yet might
     * still be necessary or useful for performance or other reasons
     *
     * @param array $query
     * @psalm-param array{repositories:array,tables?:array,fields:array,where?:array,group?:array,order?:array,limit?:int,offset?:int,lock?:bool,query?:string,parameters?:array} $query
     * @return MultiRepositorySelectQueryInterface
     */
    public function select(array $query): MultiRepositorySelectQueryInterface;

    /**
     * Find one entry from a result set and return it as an array
     *
     * @param MultiRepositorySelectQueryInterface $selectQuery
     * @return array|null
     */
    public function fetch(MultiRepositorySelectQueryInterface $selectQuery): ?array;

    /**
     * Clear existing result set
     *
     * @param MultiRepositorySelectQueryInterface $selectQuery
     */
    public function clear(MultiRepositorySelectQueryInterface $selectQuery): void;

    /**
     * Select query and return one entry. The query options are the same as with the select function
     *
     * @param array $query
     * @psalm-param array{repositories:array,tables?:array,fields:array,where?:array,group?:array,order?:array,limit?:int,offset?:int,lock?:bool,query?:string,parameters?:array} $query
     * @return array
     */
    public function fetchOne(array $query): ?array;

    /**
     * Select query and return all entries.  The query options are the same as with the select function,
     * except for flattenFields:
     *
     * 'flattenFields': Whether to return a one dimensional array of just values instead of arrays (optional)
     *
     * @param array $query
     * @psalm-param array{repositories:array,tables?:array,fields:array,where?:array,group?:array,order?:array,limit?:int,offset?:int,lock?:bool,query?:string,parameters?:array,flattenFields?:bool} $query
     * @return array
     */
    public function fetchAll(array $query): array;
}
