<?php

namespace Squirrel\Entities;

/**
 * INTERFACE: Run queries within a transaction
 */
interface TransactionInterface
{
    /**
     * Process $func within a transaction. Any additional arguments after
     * $func are passed to $func as arguments
     *
     * @param callable $func
     * @param mixed ...$arguments
     * @return mixed
     *
     * @template TReturn
     * @psalm-param callable(mixed ...$arguments): TReturn $func
     * @psalm-return TReturn
     */
    public function run(callable $func, ...$arguments);
}
