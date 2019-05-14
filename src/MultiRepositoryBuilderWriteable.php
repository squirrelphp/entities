<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Action\MultiUpdateEntries;

class MultiRepositoryBuilderWriteable extends MultiRepositoryBuilderReadOnly implements
    MultiRepositoryBuilderWriteableInterface
{
    /**
     * @var MultiRepositoryWriteableInterface
     */
    private $multiRepositoryWriteable;

    public function __construct(?MultiRepositoryWriteableInterface $multiRepositoryWriteable = null)
    {
        if ($multiRepositoryWriteable === null) {
            $multiRepositoryWriteable = new MultiRepositoryWriteable();
        }

        $this->multiRepositoryWriteable = $multiRepositoryWriteable;
        parent::__construct($multiRepositoryWriteable);
    }

    /**
     * @inheritDoc
     */
    public function update(): MultiUpdateEntries
    {
        return new MultiUpdateEntries($this->multiRepositoryWriteable);
    }
}
