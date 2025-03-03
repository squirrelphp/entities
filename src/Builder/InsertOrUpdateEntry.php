<?php

namespace Squirrel\Entities\Builder;

use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\Builder\BuilderInterface;

/**
 * Upsert query builder as a fluent object - build query and execute it
 */
class InsertOrUpdateEntry implements BuilderInterface
{
    /**
     * @var array<string,mixed> VALUES clauses for the query
     */
    private array $values = [];

    /**
     * @var string[] Unique index fields to determine when to update and when to insert
     */
    private array $index = [];

    /**
     * @var array<int|string,mixed>|null SET clauses for the update part of the query
     */
    private ?array $valuesOnUpdate = null;

    public function __construct(
        private readonly RepositoryWriteableInterface $repository,
    ) {
    }

    /**
     * @param array<string,mixed> $values
     */
    public function set(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @param string[]|string $indexFields
     * @return InsertOrUpdateEntry
     */
    public function index($indexFields): self
    {
        if (\is_string($indexFields)) {
            $indexFields = [$indexFields];
        }

        $this->index = $indexFields;
        return $this;
    }

    /**
     * @param array<int|string,mixed>|string|null $values
     * @return InsertOrUpdateEntry
     */
    public function setOnUpdate($values): self
    {
        if (\is_string($values)) {
            $values = [$values];
        }

        $this->valuesOnUpdate = $values;
        return $this;
    }

    /**
     * Write changes to database
     */
    public function write(): void
    {
        $this->repository->insertOrUpdate($this->values, $this->index, $this->valuesOnUpdate);
    }
}
