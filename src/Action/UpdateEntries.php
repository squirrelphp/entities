<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\DBDebug;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Update query builder as a fluent object - build query and execute it
 */
class UpdateEntries implements ActionInterface
{
    /**
     * @var RepositoryWriteableInterface Repository we call to execute the built query
     */
    private $repository;

    /**
     * @var array SET clauses for the query
     */
    private $changes = [];

    /**
     * @var array WHERE restrictions in query
     */
    private $where = [];

    /**
     * @var bool We need to confirmation before we execute a query without WHERE restriction
     */
    private $confirmNoWhere = false;

    public function __construct(RepositoryWriteableInterface $repository)
    {
        $this->repository = $repository;
    }

    public function set(array $changes): self
    {
        $this->changes = $changes;
        return $this;
    }

    public function where(array $whereClauses): self
    {
        $this->where = $whereClauses;
        return $this;
    }

    public function confirmNoWhereRestrictions(): self
    {
        $this->confirmNoWhere = true;
        return $this;
    }

    /**
     * Write changes to database
     */
    public function write(): void
    {
        $this->writeAndReturnAffectedNumber();
    }

    /**
     * Write changes to database and return affected entries number
     *
     * @return int Number of affected entries in database
     */
    public function writeAndReturnAffectedNumber(): int
    {
        // Make sure there is no accidental "delete everything"
        if (\count($this->where) === 0 && $this->confirmNoWhere !== true) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [ActionInterface::class],
                'No restricting "where" arguments defined for UPDATE ' .
                'and no override confirmation with "confirmNoWhereRestrictions" call'
            );
        }

        return $this->repository->update($this->changes, $this->where);
    }
}
