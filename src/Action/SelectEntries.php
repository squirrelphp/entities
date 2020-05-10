<?php

namespace Squirrel\Entities\Action;

use Squirrel\Debug\Debug;
use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Select query builder as a fluent object - build query and return object(s) or flattened fields
 *
 * @implements \IteratorAggregate<int,object>
 */
class SelectEntries implements ActionInterface, \IteratorAggregate
{
    private RepositoryReadOnlyInterface $repository;

    /**
     * @var array<int|string,mixed> WHERE restrictions in query
     */
    private array $where = [];

    /**
     * @var array<int|string,string> ORDER BY sorting in query
     */
    private array $orderBy = [];

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

    /**
     * @var string[] Only retrieve some of the fields of the objects, default is to return all
     */
    private array $fields = [];

    public function __construct(RepositoryReadOnlyInterface $repository)
    {
        $this->repository = $repository;
    }

    public function field(string $onlyGetThisField): self
    {
        $this->fields = [$onlyGetThisField];
        return $this;
    }

    /**
     * @param string[] $onlyGetTheseFields
     */
    public function fields(array $onlyGetTheseFields): self
    {
        $this->fields = $onlyGetTheseFields;
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
     *
     * Returns object[] (from the entity class), we avoid the return type hint
     * here to code analyzers don't get confused by generated repositories
     * and their different type hint
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
     *
     * Returns object (from the entity class) or null if no entry was found
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

    /**
     * @return int[]
     */
    public function getFlattenedIntegerFields(): array
    {
        $values = $this->getFlattenedFields();

        foreach ($values as $value) {
            if (!\is_int($value)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [ActionInterface::class],
                    'Flattened integers requested, but not all values were integers'
                );
            }
        }

        return $values;
    }

    /**
     * @return float[]
     */
    public function getFlattenedFloatFields(): array
    {
        $values = $this->getFlattenedFields();

        foreach ($values as $key => $value) {
            if (\is_int($value)) {
                $values[$key] = \floatval($value);
                continue;
            }

            if (!\is_float($value)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [ActionInterface::class],
                    'Flattened floats requested, but not all values were floats'
                );
            }
        }

        return $values;
    }

    /**
     * @return string[]
     */
    public function getFlattenedStringFields(): array
    {
        $values = $this->getFlattenedFields();

        foreach ($values as $value) {
            if (!\is_string($value)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [ActionInterface::class],
                    'Flattened strings requested, but not all values were strings'
                );
            }
        }

        return $values;
    }

    /**
     * @return bool[]
     */
    public function getFlattenedBooleanFields(): array
    {
        $values = $this->getFlattenedFields();

        foreach ($values as $value) {
            if (!\is_bool($value)) {
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [ActionInterface::class],
                    'Flattened booleans requested, but not all values were booleans'
                );
            }
        }

        return $values;
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
