<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryReadOnlyInterface;

/**
 * Select query builder as a fluent object - build query and return object(s) or flattened fields
 */
class SelectEntries implements ActionInterface
{
    /**
     * @var RepositoryReadOnlyInterface Repository we call to execute the built query
     */
    private $repository;

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
     * @var int Where in the result set to start (so many entries are skipped)
     */
    private $startAt = 0;

    /**
     * @var bool Whether the SELECT query should block the scanned entries
     */
    private $blocking = false;

    /**
     * @var array Only retrieve some of the fields of the objects, default is to return all
     */
    private $fields = [];

    public function __construct(RepositoryReadOnlyInterface $repository)
    {
        $this->repository = $repository;
    }

    public function field(string $onlyGetThisField): self
    {
        $this->fields = [$onlyGetThisField];
        return $this;
    }

    public function fields(array $onlyGetTheseFields): self
    {
        $this->fields = $onlyGetTheseFields;
        return $this;
    }

    public function where(array $whereClauses): self
    {
        $this->where = $whereClauses;
        return $this;
    }

    /**
     * @param array|string $orderByClauses
     * @return SelectEntries
     */
    public function orderBy($orderByClauses): self
    {
        if (\is_string($orderByClauses)) {
            $orderByClauses = [$orderByClauses];
        }

        $this->orderBy = $orderByClauses;
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
     * Execute SELECT query and return a list of objects that matched it
     */
    public function getEntries(): array
    {
        return $this->repository->select([
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
    public function getOneEntry()
    {
        return $this->repository->selectOne([
            'where' => $this->where,
            'order' => $this->orderBy,
            'fields' => $this->fields,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
        ]);
    }

    /**
     * Execute SELECT query and return the fields as a list of values - no objects
     *
     * @return string[]|int[]|bool[]|null[]
     */
    public function getFlattenedFields(): array
    {
        return $this->repository->selectFlattenedFields([
            'where' => $this->where,
            'order' => $this->orderBy,
            'fields' => $this->fields,
            'limit' => $this->limitTo,
            'offset' => $this->startAt,
            'lock' => $this->blocking,
        ]);
    }
}
