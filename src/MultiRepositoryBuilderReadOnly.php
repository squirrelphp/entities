<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Builder\MultiCountEntries;
use Squirrel\Entities\Builder\MultiSelectEntries;
use Squirrel\Entities\Builder\MultiSelectEntriesFreeform;

class MultiRepositoryBuilderReadOnly implements MultiRepositoryBuilderReadOnlyInterface
{
    private MultiRepositoryReadOnlyInterface $multiRepositoryReadOnly;

    public function __construct(?MultiRepositoryReadOnlyInterface $multiRepositoryReadOnly = null)
    {
        if ($multiRepositoryReadOnly === null) {
            $multiRepositoryReadOnly = new MultiRepositoryReadOnly();
        }

        $this->multiRepositoryReadOnly = $multiRepositoryReadOnly;
    }

    public function select(): MultiSelectEntries
    {
        return new MultiSelectEntries($this->multiRepositoryReadOnly);
    }

    public function selectFreeform(): MultiSelectEntriesFreeform
    {
        return new MultiSelectEntriesFreeform($this->multiRepositoryReadOnly);
    }

    public function count(): MultiCountEntries
    {
        return new MultiCountEntries($this->multiRepositoryReadOnly);
    }
}
