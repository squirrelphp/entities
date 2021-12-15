<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\Builder\CountEntries;
use Squirrel\Entities\Builder\SelectEntries;
use Squirrel\Entities\RepositoryBuilderReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class TicketRepositoryBuilderReadOnly implements RepositoryBuilderReadOnlyInterface
{
    public function __construct(
        private RepositoryReadOnlyInterface $repository,
    ) {
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
