<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryWriteableInterface;

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
     * @var array ORDER BY sorting in query
     */
    private $orderBy = [];

    /**
     * @var int How many results should be returned
     */
    private $limitTo = 0;

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

    /**
     * @param array|string $orderByClauses
     * @return UpdateEntries
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

    /**
     * Write changes to database
     */
    public function write(): void
    {
        $this->repository->update([
            'changes' => $this->changes,
            'where' => $this->where,
            'order' => $this->orderBy,
            'limit' => $this->limitTo,
        ]);
    }

    /**
     * Write changes to database and return affected entries number
     *
     * @return int Number of affected entries in database
     */
    public function writeAndReturnAffectedNumber(): int
    {
        return $this->repository->update([
            'changes' => $this->changes,
            'where' => $this->where,
            'order' => $this->orderBy,
            'limit' => $this->limitTo,
        ]);
    }
}
