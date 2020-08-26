<?php

namespace Squirrel\Entities\Builder;

use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Entities\RepositorySelectQueryInterface;
use Squirrel\Queries\Builder\BuilderInterface;
use Squirrel\Queries\Builder\SelectIteratorTrait;

/**
 * Iterator returned by SelectEntries to be used in a foreach loop
 *
 * @implements \Iterator<int,object>
 */
class SelectIterator implements \Iterator, BuilderInterface
{
    use SelectIteratorTrait;

    private RepositoryReadOnlyInterface $source;
    private ?RepositorySelectQueryInterface $selectReference = null;
    private ?object $lastResult = null;

    public function __construct(RepositoryReadOnlyInterface $repository, array $query)
    {
        $this->source = $repository;
        $this->query = $query;
    }

    public function current(): object
    {
        // @codeCoverageIgnoreStart
        if ($this->lastResult === null) {
            throw new \LogicException('Cannot get current value if no result has been retrieved');
        }
        // @codeCoverageIgnoreEnd

        return $this->lastResult;
    }
}
