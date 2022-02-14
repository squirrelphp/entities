<?php

namespace Squirrel\Entities\Builder;

use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Queries\Builder\BuilderInterface;
use Squirrel\Queries\Builder\FlattenedFieldsWithTypeTrait;

/*
 * Select query builder as a fluent object - build query and return object(s) or flattened fields
 *
 * Properties are only protected so we can extend it with generated repositories
 */
class SelectEntries implements BuilderInterface, \IteratorAggregate
{
    use FlattenedFieldsWithTypeTrait;

    /**
     * @var array<int|string,mixed> WHERE restrictions in query
     */
    protected array $where = [];

    /**
     * @var array<int|string,string> ORDER BY sorting in query
     */
    protected array $orderBy = [];

    /**
     * @var int How many results should be returned
     */
    protected int $limitTo = 0;

    /**
     * @var int Where in the result set to start (so many entries are skipped)
     */
    protected int $startAt = 0;

    /**
     * @var bool Whether the SELECT query should block the scanned entries
     */
    protected bool $blocking = false;

    /**
     * @var string[] Only retrieve some of the fields of the objects, default is to return all
     */
    protected array $fields = [];

    public function __construct(
        protected RepositoryReadOnlyInterface $repository,
    ) {
    }

    public function field(string $onlyGetThisField): static
    {
        $this->fields = [$onlyGetThisField];
        return $this;
    }

    /**
     * @param string[] $onlyGetTheseFields
     */
    public function fields(array $onlyGetTheseFields): static
    {
        $this->fields = $onlyGetTheseFields;
        return $this;
    }

    /**
     * @param array<int|string,mixed> $whereClauses
     */
    public function where(array $whereClauses): static
    {
        $this->where = $whereClauses;
        return $this;
    }

    /**
     * @param array<int|string,string>|string $orderByClauses
     */
    public function orderBy(array|string $orderByClauses): static
    {
        if (\is_string($orderByClauses)) {
            $orderByClauses = [$orderByClauses];
        }

        $this->orderBy = $orderByClauses;
        return $this;
    }

    public function startAt(int $startAtNumber): static
    {
        $this->startAt = $startAtNumber;
        return $this;
    }

    public function limitTo(int $numberOfEntries): static
    {
        $this->limitTo = $numberOfEntries;
        return $this;
    }

    public function blocking(bool $active = true): static
    {
        $this->blocking = $active;
        return $this;
    }

    /**
     * Execute SELECT query and return a list of objects that matched it
     *
     * @return object[]
     */
    public function getAllEntries(): array
    {
        return $this->repository->fetchAll([
            'where' => $this->where,
            'order' => $this->orderBy,
            'fields' => $this->fields,
            'limit' => $this->limitTo,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
        ]);
    }

    /**
     * Execute SELECT query and return exactly one entry, if one was found at all
     */
    public function getOneEntry(): ?object
    {
        return $this->repository->fetchOne([
            'where' => $this->where,
            'order' => $this->orderBy,
            'fields' => $this->fields,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
        ]);
    }

    /**
     * Execute SELECT query and return the fields as a list of flattened values - no objects
     *
     * @return array<bool|int|float|string|null>
     */
    public function getFlattenedFields(): array
    {
        return $this->repository->fetchAllAndFlatten([
            'where' => $this->where,
            'order' => $this->orderBy,
            'fields' => $this->fields,
            'limit' => $this->limitTo,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
        ]);
    }

    public function getIterator(): SelectIterator
    {
        return new SelectIterator($this->repository, [
            'where' => $this->where,
            'order' => $this->orderBy,
            'fields' => $this->fields,
            'limit' => $this->limitTo,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
        ]);
    }
}
