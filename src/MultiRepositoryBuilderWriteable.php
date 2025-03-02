<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Builder\MultiUpdateEntriesFreeform;

final readonly class MultiRepositoryBuilderWriteable extends MultiRepositoryBuilderReadOnly implements MultiRepositoryBuilderWriteableInterface
{
    public function __construct(
        private MultiRepositoryWriteableInterface $multiRepositoryWriteable = new MultiRepositoryWriteable(),
    ) {
        parent::__construct($this->multiRepositoryWriteable);
    }

    public function updateFreeform(): MultiUpdateEntriesFreeform
    {
        return new MultiUpdateEntriesFreeform($this->multiRepositoryWriteable);
    }
}
