<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Action\MultiCountEntries;
use Squirrel\Entities\Action\MultiSelectEntries;
use Squirrel\Entities\Action\MultiSelectEntriesFreeform;

class MultiRepositoryBuilderReadOnly implements MultiRepositoryBuilderReadOnlyInterface
{
    /**
     * @var MultiRepositoryReadOnlyInterface
     */
    private $multiRepositoryReadOnly;

    public function __construct(?MultiRepositoryReadOnlyInterface $multiRepositoryReadOnly = null)
    {
        if ($multiRepositoryReadOnly === null) {
            $multiRepositoryReadOnly = new MultiRepositoryReadOnly();
        }

        $this->multiRepositoryReadOnly = $multiRepositoryReadOnly;
    }

    /**
     * @inheritDoc
     */
    public function select(): MultiSelectEntries
    {
        return new MultiSelectEntries($this->multiRepositoryReadOnly);
    }

    /**
     * @inheritDoc
     */
    public function selectFreeform(): MultiSelectEntriesFreeform
    {
        return new MultiSelectEntriesFreeform($this->multiRepositoryReadOnly);
    }

    /**
     * @inheritDoc
     */
    public function count(): MultiCountEntries
    {
        return new MultiCountEntries($this->multiRepositoryReadOnly);
    }
}
