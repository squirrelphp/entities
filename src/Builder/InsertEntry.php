<?php

namespace Squirrel\Entities\Builder;

use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\Builder\BuilderInterface;

/**
 * Insert query builder as a fluent object - build query and execute it
 */
class InsertEntry implements BuilderInterface
{
    /**
     * @var array<string,mixed> VALUES clauses for the query
     */
    private array $values = [];

    public function __construct(
        private RepositoryWriteableInterface $repository,
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
