<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\Action\CountEntries;
use Squirrel\Entities\Action\SelectEntries;
use Squirrel\Entities\RepositoryBuilderReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class TicketRepositoryBuilderReadOnly implements RepositoryBuilderReadOnlyInterface
{
    /**
     * @var RepositoryReadOnlyInterface
     */
    private $repository;

    public function __construct(RepositoryReadOnlyInterface $repository)
    {
        $this->repository = $repository;
    }

    public function select(): SelectEntries
    {
        return new SelectEntries($this->repository);
    }

    public function count(): CountEntries
    {
        return new CountEntries($this->repository);
    }
}
