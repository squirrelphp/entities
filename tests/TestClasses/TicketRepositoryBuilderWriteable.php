<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\Builder\DeleteEntries;
use Squirrel\Entities\Builder\InsertEntry;
use Squirrel\Entities\Builder\InsertOrUpdateEntry;
use Squirrel\Entities\Builder\UpdateEntries;
use Squirrel\Entities\RepositoryBuilderWriteableInterface;
use Squirrel\Entities\RepositoryWriteableInterface;

class TicketRepositoryBuilderWriteable extends TicketRepositoryBuilderReadOnly implements
    RepositoryBuilderWriteableInterface
{
    public function __construct(
        private RepositoryWriteableInterface $repository,
    ) {
        parent::__construct($repository);
    }

    public function insert(): InsertEntry
    {
        return new InsertEntry($this->repository);
    }

    public function insertOrUpdate(): InsertOrUpdateEntry
    {
        return new InsertOrUpdateEntry($this->repository);
    }

    public function update(): UpdateEntries
    {
        return new UpdateEntries($this->repository);
    }

    public function delete(): DeleteEntries
    {
        return new DeleteEntries($this->repository);
    }
}
