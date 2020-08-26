<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Builder\MultiCountEntries;
use Squirrel\Entities\Builder\MultiSelectEntries;
use Squirrel\Entities\Builder\MultiSelectEntriesFreeform;

interface MultiRepositoryBuilderReadOnlyInterface
{
    /**
     * @return MultiSelectEntries Query builder for a multi-repository select query
     */
    public function select(): MultiSelectEntries;

    /**
     * @return MultiSelectEntriesFreeform Query builder for a multi-repository freeform select query
     */
    public function selectFreeform(): MultiSelectEntriesFreeform;

    /**
     * @return MultiCountEntries Query builder for a multi-repository select count query
     */
    public function count(): MultiCountEntries;
}
