<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Entities\RepositorySelectQueryInterface;
use Squirrel\Queries\Builder\SelectIteratorTrait;

/**
 * Iterator returned by SelectEntries to be used in a foreach loop
 *
 * @implements \Iterator<int,object>
 */
class SelectIterator implements \Iterator, ActionInterface
{
    use SelectIteratorTrait;

    /**
     * @var RepositoryReadOnlyInterface
     */
    private $source;

    /**
     * @var RepositorySelectQueryInterface|null
     */
    private $selectReference;

    public function __construct(RepositoryReadOnlyInterface $repository, array $query)
    {
        $this->source = $repository;
        $this->query = $query;
    }
}
