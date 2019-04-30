<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\MultiRepositoryWriteableInterface;
use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\DBDebug;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Update query builder as a fluent object - build query and execute it
 */
class MultiUpdateEntries implements ActionInterface
{
    /**
     * @var MultiRepositoryWriteableInterface
     */
    private $queryHandler;

    /**
     * @var RepositoryWriteableInterface[] Repositories used in the multi query
     */
    private $repositories = [];

    /**
     * @var array Explicit connections between the repositories
     */
    private $connections = [];

    /**
     * @var array SET clauses for the query
     */
    private $changes = [];

    /**
     * @var array WHERE restrictions in query
     */
    private $where = [];

    /**
     * @var array ORDER BY sorting in query
     */
    private $orderBy = [];

    /**
     * @var int How many results should be returned
     */
    private $limitTo = 0;

    /**
     * @var bool We need to confirmation before we execute a query without WHERE restriction
     */
    private $confirmNoWhere = false;

    public function __construct(MultiRepositoryWriteableInterface $queryHandler)
    {
        $this->queryHandler = $queryHandler;
    }

    public function inRepositories(array $repositories): self
    {
        $this->repositories = $repositories;
        return $this;
    }

    public function joinTables(array $repositoryConnections): self
    {
        $this->connections = $repositoryConnections;
        return $this;
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

    /**
     * @param array|string $orderByClauses
     * @return MultiUpdateEntries
     */
    public function orderBy($orderByClauses): self
    {
        if (\is_string($orderByClauses)) {
            $orderByClauses = [$orderByClauses];
        }

        $this->orderBy = $orderByClauses;
        return $this;
    }

    public function limitTo(int $numberOfEntries): self
    {
        $this->limitTo = $numberOfEntries;
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

        return $this->queryHandler->update([
            'repositories' => $this->repositories,
            'tables' => $this->connections,
            'changes' => $this->changes,
            'where' => $this->where,
            'order' => $this->orderBy,
            'limit' => $this->limitTo,
        ]);
    }
}
