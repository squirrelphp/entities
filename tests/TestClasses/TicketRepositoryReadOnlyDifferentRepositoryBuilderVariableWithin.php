<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\Action\CountEntries;
use Squirrel\Entities\Action\SelectEntries;
use Squirrel\Entities\RepositoryBuilderReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class TicketRepositoryReadOnlyDifferentRepositoryBuilderVariableWithin implements RepositoryBuilderReadOnlyInterface
{
    /**
     * @var RepositoryReadOnlyInterface
     */
    private $repositoryDifferentName;

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
