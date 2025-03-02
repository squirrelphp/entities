<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Builder\MultiCountEntries;
use Squirrel\Entities\Builder\MultiSelectEntries;
use Squirrel\Entities\Builder\MultiSelectEntriesFreeform;

readonly class MultiRepositoryBuilderReadOnly implements MultiRepositoryBuilderReadOnlyInterface
{
    public function __construct(
        private MultiRepositoryReadOnlyInterface $multiRepositoryReadOnly = new MultiRepositoryReadOnly(),
    ) {
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
