<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Action\MultiUpdateEntries;

interface MultiRepositoryBuilderWriteableInterface extends MultiRepositoryBuilderReadOnlyInterface
{
    /**
     * @return MultiUpdateEntries Query builder for a multi-repository update query
     */
    public function update(): MultiUpdateEntries;
}
