<?php

namespace Squirrel\Entities;

/**
 * INTERFACE: Multiple repositories should be written to (UPDATE) or read from (SELECT)
 */
interface MultiRepositoryWriteableInterface extends MultiRepositoryReadOnlyInterface
{
    /**
     * Update query. The options can be:
     *
     * - 'repositories': involved repositories as a name to RepositoryConfigInterface list
     * - 'tables':  how tables are connected and which are selected (optional, default is all)
     * - 'changes': changes to table fields
     * - 'where':   WHERE restrictions
     * - 'order':   ORDER BY definitions (optional)
     * - 'limit':   how many results to update (optional)
     *
     * A special possibility is a freeform query, with the following options:
     *
     * - 'repositories': involved repositories as a name to RepositoryConfigInterface list
     * - 'query':  Query as a string
     * - 'parameters': Array of query value parameters
     *
     * Freeform queries should almost never be necessary and is considered bad practice, yet it is still a possibility
     * because some queries (for example with subqueries or other complicated parts) cannot be structured yet might
     * still be necessary or useful for performance or other reasons
     *
     * @param array $query
     * @psalm-param array{repositories:array,tables?:array,changes?:array,where?:array,order?:array,limit?:int,query?:string,parameters?:array} $query
     * @return int
     */
    public function update(array $query): int;
}
