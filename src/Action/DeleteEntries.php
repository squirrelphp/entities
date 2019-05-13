<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\DBDebug;
use Squirrel\Queries\Exception\DBInvalidOptionException;

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

    /**
     * @var bool We need to confirmation before we execute a query without WHERE restriction
     */
    private $confirmNoWhere = false;

    public function __construct(RepositoryWriteableInterface $repository)
    {
        $this->repository = $repository;
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
        $this->accidentalDeleteAllCheck();

        return $this->repository->delete($this->where);
    }

    /**
     * Make sure there is no accidental "delete everything" because WHERE restrictions were forgotten
     */
    private function accidentalDeleteAllCheck(): void
    {
        // Make sure there is no accidental "delete everything"
        if (\count($this->where) === 0 && $this->confirmNoWhere !== true) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [ActionInterface::class],
                'No restricting "where" arguments defined for DELETE ' .
                'and no override confirmation with "confirmNoWhereRestrictions" call'
            );
        }
    }
}
