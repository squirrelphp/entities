<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\Builder\CountEntries;
use Squirrel\Entities\Builder\SelectEntries;
use Squirrel\Entities\RepositoryBuilderReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class TicketRepositoryReadOnlyDifferentRepositoryBuilderVariableWithin implements RepositoryBuilderReadOnlyInterface
{
    private RepositoryReadOnlyInterface $repositoryDifferentName;

    public function __construct(RepositoryReadOnlyInterface $repository)
    {
        $this->repositoryDifferentName = $repository;
    }

    public function select(): SelectEntries
    {
        return new SelectEntries($this->repositoryDifferentName);
    }

    public function count(): CountEntries
    {
        return new CountEntries($this->repositoryDifferentName);
    }
}
