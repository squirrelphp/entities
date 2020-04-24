<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

/**
 * Select query builder as a fluent object - build query and return object(s) or flattened fields
 */
class MultiCountEntries implements ActionInterface
{
    private MultiRepositoryReadOnlyInterface $queryHandler;

    /**
     * @var RepositoryReadOnlyInterface[] Repositories used in the multi query
     */
    private array $repositories = [];

    /**
     * @var array<int|string,mixed> Explicit connections between the repositories
     */
    private array $connections = [];

    /**
     * @var array<int|string,mixed> WHERE restrictions in query
     */
    private array $where = [];

    /**
     * @var bool Whether the SELECT query should block the scanned entries
     */
    private bool $blocking = false;

    public function __construct(MultiRepositoryReadOnlyInterface $queryHandler)
    {
        $this->queryHandler = $queryHandler;
    }

    /**
     * @param RepositoryReadOnlyInterface[] $repositories
     */
    public function inRepositories(array $repositories): self
    {
        $this->repositories = $repositories;
        return $this;
    }

    /**
     * @param array<int|string,mixed> $repositoryConnections
     */
    public function joinTables(array $repositoryConnections): self
    {
        $this->connections = $repositoryConnections;
        return $this;
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

    /**
     * Execute SELECT query and return number of entries
     */
    public function getNumber(): int
    {
        return $this->queryHandler->count([
            'repositories' => $this->repositories,
            'tables' => $this->connections,
            'where' => $this->where,
            'lock' => $this->blocking,
        ]);
    }
}
