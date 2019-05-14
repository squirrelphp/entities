<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Action\MultiUpdateEntriesFreeform;

interface MultiRepositoryBuilderWriteableInterface extends MultiRepositoryBuilderReadOnlyInterface
{
    /**
     * @return MultiUpdateEntriesFreeform Query builder for a multi-repository update query
     */
    public function updateFreeform(): MultiUpdateEntriesFreeform;
}
