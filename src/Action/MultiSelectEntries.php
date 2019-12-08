<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

/**
 * Select query builder as a fluent object - build query and return entries or flattened fields
 */
class MultiSelectEntries implements ActionInterface, \IteratorAggregate
{
    /**
     * @var MultiRepositoryReadOnlyInterface
     */
    private $queryHandler;

    /**
     * @var array<int|string,string> Only retrieve these fields of the repositories
     */
    private $fields = [];

    /**
     * @var RepositoryReadOnlyInterface[] Repositories used in the multi query
     */
    private $repositories = [];

    /**
     * @var array<int|string,mixed> Explicit connections between the repositories
     */
    private $connections = [];

    /**
     * @var array<int|string,mixed> WHERE restrictions in query
     */
    private $where = [];

    /**
     * @var array<int|string,string> ORDER BY sorting in query
     */
    private $orderBy = [];

    /**
     * @var array<int|string,string> GROUP BY aggregating in query
     */
    private $groupBy = [];

    /**
     * @var int How many results should be returned
     */
    private $limitTo = 0;

    /**
     * @var int Where in the result set to start (so many entries are skipped)
     */
    private $startAt = 0;

    /**
     * @var bool Whether the SELECT query should block the scanned entries
     */
    private $blocking = false;

    public function __construct(MultiRepositoryReadOnlyInterface $queryHandler)
    {
        $this->queryHandler = $queryHandler;
    }

    public function field(string $getThisField): self
    {
        $this->fields = [$getThisField];
        return $this;
    }

    /**
     * @param array<int|string,string> $getTheseFields
     */
    public function fields(array $getTheseFields): self
    {
        $this->fields = $getTheseFields;
        return $this;
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

    /**
     * @param array<int|string,string>|string $orderByClauses
     * @return MultiSelectEntries
     */
    public function orderBy($orderByClauses): self
    {
        if (\is_string($orderByClauses)) {
            $orderByClauses = [$orderByClauses];
        }

        $this->orderBy = $orderByClauses;
        return $this;
    }

    /**
     * @param array<int|string,string>|string $groupByClauses
     * @return MultiSelectEntries
     */
    public function groupBy($groupByClauses): self
    {
        if (\is_string($groupByClauses)) {
            $groupByClauses = [$groupByClauses];
        }

        $this->groupBy = $groupByClauses;
        return $this;
    }

    public function startAt(int $startAtNumber): self
    {
        $this->startAt = $startAtNumber;
        return $this;
    }

    public function limitTo(int $numberOfEntries): self
    {
        $this->limitTo = $numberOfEntries;
        return $this;
    }

    public function blocking(bool $active = true): self
    {
        $this->blocking = $active;
        return $this;
    }

    /**
     * Execute SELECT query and return a list of entries as arrays that matched it
     */
    public function getAllEntries(): array
    {
        return $this->queryHandler->fetchAll([
            'fields' => $this->fields,
            'repositories' => $this->repositories,
            'tables' => $this->connections,
            'where' => $this->where,
            'order' => $this->orderBy,
            'group' => $this->groupBy,
            'limit' => $this->limitTo,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
        ]);
    }

    /**
     * Execute SELECT query and return exactly one entry, if one was found at all
     *
     * @return array<string,mixed>|null
     */
    public function getOneEntry(): ?array
    {
        return $this->queryHandler->fetchOne([
            'fields' => $this->fields,
            'repositories' => $this->repositories,
            'tables' => $this->connections,
            'where' => $this->where,
            'order' => $this->orderBy,
            'group' => $this->groupBy,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
        ]);
    }

    /**
     * Execute SELECT query and return the fields as a list of values
     *
     * @return array<int,mixed>
     */
    public function getFlattenedFields(): array
    {
        return $this->queryHandler->fetchAll([
            'fields' => $this->fields,
            'repositories' => $this->repositories,
            'tables' => $this->connections,
            'where' => $this->where,
            'order' => $this->orderBy,
            'group' => $this->groupBy,
            'limit' => $this->limitTo,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
            'flattenFields' => true,
        ]);
    }

    public function getIterator(): MultiSelectIterator
    {
        return new MultiSelectIterator($this->queryHandler, [
            'fields' => $this->fields,
            'repositories' => $this->repositories,
            'tables' => $this->connections,
            'where' => $this->where,
            'order' => $this->orderBy,
            'group' => $this->groupBy,
            'limit' => $this->limitTo,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
        ]);
    }
}
