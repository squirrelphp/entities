<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryReadOnlyInterface;

/**
 * Count query builder as a fluent object - build query and return number
 */
class CountEntries implements ActionInterface
{
    /**
     * @var RepositoryReadOnlyInterface Repository we call to execute the built query
     */
    private $repository;

    /**
     * @var array<int|string,mixed> WHERE restrictions in query
     */
    private $where = [];

    /**
     * @var bool Whether the SELECT query should block the scanned entries
     */
    private $blocking = false;

    public function __construct(RepositoryReadOnlyInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array<int|string,mixed> $whereClauses
     */
    public function where(array $whereClauses): self
    {
        $this->where = $whereClauses;
        return $this;
    }

    public function blocking(bool $active = true): self
    {
        $this->blocking = $active;
        return $this;
    }

    public function getNumber(): int
    {
        return $this->repository->count([
            'where' => $this->where,
            'lock' => $this->blocking,
        ]);
    }
}
