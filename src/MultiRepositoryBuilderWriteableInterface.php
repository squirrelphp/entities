<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Action\MultiUpdateEntries;
use Squirrel\Entities\Action\MultiUpdateEntriesFreeform;

interface MultiRepositoryBuilderWriteableInterface extends MultiRepositoryBuilderReadOnlyInterface
{
    /**
     * @return MultiUpdateEntries Query builder for a multi-repository update query
     */
    public function update(): MultiUpdateEntries;

    /**
     * @return MultiUpdateEntriesFreeform Query builder for a multi-repository freeform update query
     */
    public function updateFreeform(): MultiUpdateEntriesFreeform;
}
