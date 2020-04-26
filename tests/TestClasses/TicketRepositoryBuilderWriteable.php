<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\Action\DeleteEntries;
use Squirrel\Entities\Action\InsertEntry;
use Squirrel\Entities\Action\InsertOrUpdateEntry;
use Squirrel\Entities\Action\UpdateEntries;
use Squirrel\Entities\RepositoryBuilderWriteableInterface;
use Squirrel\Entities\RepositoryWriteableInterface;

class TicketRepositoryBuilderWriteable extends TicketRepositoryBuilderReadOnly implements
    RepositoryBuilderWriteableInterface
{
    private RepositoryWriteableInterface $repository;

    public function __construct(RepositoryWriteableInterface $repository)
    {
        $this->repository = $repository;
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
