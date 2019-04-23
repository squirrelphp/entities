<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

/**
 * Select query builder as a fluent object - build query and return object(s) or flattened fields
 */
class MultiCountEntries implements ActionInterface
{
    /**
     * @var MultiRepositoryReadOnlyInterface
     */
    private $queryHandler;

    /**
     * @var RepositoryReadOnlyInterface[] Repositories used in the multi query
     */
    private $repositories = [];

    /**
     * @var array Explicit connections between the repositories
     */
    private $connections = [];

    /**
     * @var array WHERE restrictions in query
     */
    private $where = [];

    /**
     * @var bool Whether the SELECT query should block the scanned entries
     */
    private $blocking = false;

    public function __construct(MultiRepositoryReadOnlyInterface $queryHandler)
    {
        $this->queryHandler = $queryHandler;
    }

    public function inRepositories(array $repositories)
    {
        $this->repositories = $repositories;
    }

    public function connectedBy(array $repositoryConnections)
    {
        $this->connections = $repositoryConnections;
    }

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
        $results = $this->queryHandler->select([
            'fields' => [
                'num' => 'COUNT(*)',
            ],
            'repositories' => $this->repositories,
            'tables' => $this->connections,
            'where' => $this->where,
            'flattenFields' => true,
            'lock' => $this->blocking,
        ]);

        return $results[0] ?? 0;
    }
}
