<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryWriteableInterface;

/**
 * Delete query builder as a fluent object - build query and execute it
 */
class DeleteEntries implements ActionInterface
{
    /**
     * @var RepositoryWriteableInterface Repository we call to execute the built query
     */
    private $repository;

    /**
     * @var array WHERE restrictions in query
     */
    private $where = [];

    public function __construct(RepositoryWriteableInterface $repository)
    {
        $this->repository = $repository;
    }

    public function where(array $whereClauses): self
    {
        $this->where = $whereClauses;
        return $this;
    }

    /**
     * Write changes to database
     */
    public function write(): void
    {
        $this->repository->delete($this->where);
    }

    /**
     * Write changes to database and return affected entries number
     *
     * @return int Number of affected entries in database
     */
    public function writeAndReturnAffectedNumber(): int
    {
        return $this->repository->delete($this->where);
    }
}
