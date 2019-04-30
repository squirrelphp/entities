<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryWriteableInterface;

/**
 * Upsert query builder as a fluent object - build query and execute it
 */
class InsertOrUpdateEntry implements ActionInterface
{
    /**
     * @var RepositoryWriteableInterface Repository we call to execute the built query
     */
    private $repository;

    /**
     * @var array VALUES clauses for the query
     */
    private $values = [];

    /**
     * @var array Unique index fields to determine when to update and when to insert
     */
    private $index = [];

    /**
     * @var array SET clauses for the update part of the query
     */
    private $valuesOnUpdate = [];

    public function __construct(RepositoryWriteableInterface $repository)
    {
        $this->repository = $repository;
    }

    public function set(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @param array|string $indexFields
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
     * @param array|string $values
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
        $this->writeAndReturnWhatHappened();
    }

    /**
     * Write changes to database and return the operation that happened in the database:
     *
     * "insert": Entry was inserted
     * "update": Existing entry was updated
     * "":       Nothing changed, existing entry was already up-to-date
     *
     * @return string Either returns "insert", "update" or "" to indicate what operation happened
     */
    public function writeAndReturnWhatHappened(): string
    {
        return $this->repository->insertOrUpdate($this->values, $this->index, $this->valuesOnUpdate);
    }
}
