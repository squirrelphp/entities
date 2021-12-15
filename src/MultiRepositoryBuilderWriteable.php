<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Builder\MultiUpdateEntriesFreeform;

class MultiRepositoryBuilderWriteable extends MultiRepositoryBuilderReadOnly implements
    MultiRepositoryBuilderWriteableInterface
{
    private MultiRepositoryWriteableInterface $multiRepositoryWriteable;

    public function __construct(?MultiRepositoryWriteableInterface $multiRepositoryWriteable = null)
    {
        if ($multiRepositoryWriteable === null) {
            $multiRepositoryWriteable = new MultiRepositoryWriteable();
        }

        $this->multiRepositoryWriteable = $multiRepositoryWriteable;

        parent::__construct($multiRepositoryWriteable);
    }

    public function updateFreeform(): MultiUpdateEntriesFreeform
    {
        return new MultiUpdateEntriesFreeform($this->multiRepositoryWriteable);
    }
}
