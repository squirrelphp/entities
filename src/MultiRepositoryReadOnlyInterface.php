<?php

namespace Squirrel\Entities;

/**
 * INTERFACE: Multiple repositories should be read from (SELECT)
 */
interface MultiRepositoryReadOnlyInterface
{
    /**
     * Select query. The options can be:
     *
     * - 'repositories': involved repositories as a name to RepositoryConfigInterface / RepositoryReadOnly list
     * - 'tables': how tables are connected and which are selected (optional, default is all)
     * - 'fields': what to select as a name to field name list
     * - 'where':  WHERE restrictions
     * - 'group':  GROUP BY definitions (optional)
     * - 'order':  ORDER BY definitions (optional)
     * - 'limit':  how many results to get (optional)
     * - 'offset': at what record number to start (optional)
     * - 'flattenFields': Whether to return a one dimensional array of just values instead of arrays (optional)
     * - 'lock':   if to lock selected entries (SELECT ... FOR UPDATE) for transaction (optional)
     *
     * A special possibility is a freeform query, with the following options:
     *
     * - 'repositories': involved repositories as a name to RepositoryConfigInterface list
     * - 'fields': what to select as a name to field name list
     * - 'query':  Query as a string
     * - 'parameters': Array of query value parameters
     * - 'flattenFields': Whether to return a one dimensional array of just values instead of arrays (optional)
     * - 'lock':   if to lock selected entries (SELECT ... FOR UPDATE) for transaction (optional)
     *
     * Freeform queries should almost never be necessary and is considered bad practice, yet it is still a possibility
     * because some queries (for example with subqueries or other complicated parts) cannot be structured yet might
     * still be necessary or useful for performance or other reasons
     *
     * @param array $query
     * @return array
     */
    public function select(array $query): array;

    /**
     * Select query and return one entry. The query options are the same as with the select function
     *
     * @param array $query
     * @return array
     */
    public function selectOne(array $query): ?array;

    /**
     * Select query where the results are flattened to field values, no field names / entries.
     *
     * The query options are otherwise identical to the select function
     *
     * @param array $query
     * @return array
     */
    public function selectFlattenedFields(array $query): array;
}
