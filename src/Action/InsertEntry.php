<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryWriteableInterface;

/**
 * Insert query builder as a fluent object - build query and execute it
 */
class InsertEntry implements ActionInterface
{
    /**
     * @var RepositoryWriteableInterface Repository we call to execute the built query
     */
    private $repository;

    /**
     * @var array<string,mixed> VALUES clauses for the query
     */
    private $values = [];

    public function __construct(RepositoryWriteableInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array<string,mixed> $values
     * @return $this
     */
    public function set(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Write changes to database
     */
    public function write(): void
    {
        $this->repository->insert($this->values);
    }

    /**
     * Write changes to database and return new insert ID
     *
     * @return string Return new autoincrement insert ID from database
     */
    public function writeAndReturnNewId(): string
    {
        // ?? clause only included to make it explicit for linters that we always return a string
        return $this->repository->insert($this->values, true) ?? '';
    }
}
