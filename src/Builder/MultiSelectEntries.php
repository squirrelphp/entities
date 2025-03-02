<?php

namespace Squirrel\Entities\Builder;

use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\RepositoryBuilderReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Queries\Builder\BuilderInterface;
use Squirrel\Queries\Builder\FlattenedFieldsWithTypeTrait;

/**
 * Select query builder as a fluent object - build query and return entries or flattened fields
 *
 * @implements \IteratorAggregate<int,array<string,mixed>>
 */
class MultiSelectEntries implements BuilderInterface, \IteratorAggregate
{
    use FlattenedFieldsWithTypeTrait;

    /**
     * @var array<int|string,string> Only retrieve these fields of the repositories
     */
    private array $fields = [];

    /**
     * @var array<string,RepositoryBuilderReadOnlyInterface|RepositoryReadOnlyInterface> Repositories used in the multi query
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
     * @var array<int|string,string> ORDER BY sorting in query
     */
    private array $orderBy = [];

    /**
     * @var array<int|string,string> GROUP BY aggregating in query
     */
    private array $groupBy = [];

    /**
     * @var int How many results should be returned
     */
    private int $limitTo = 0;

    /**
     * @var int Where in the result set to start (so many entries are skipped)
     */
    private int $startAt = 0;

    /**
     * @var bool Whether the SELECT query should block the scanned entries
     */
    private bool $blocking = false;

    public function __construct(
        private readonly MultiRepositoryReadOnlyInterface $queryHandler,
    ) {
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
     * @param array<string,RepositoryBuilderReadOnlyInterface|RepositoryReadOnlyInterface> $repositories
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
     * @return array<bool|int|float|string|null>
     */
    public function getFlattenedFields(): array
    {
        return $this->queryHandler->fetchAllAndFlatten([
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
