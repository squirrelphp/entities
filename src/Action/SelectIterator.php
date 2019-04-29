<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Entities\RepositorySelectQueryInterface;

/**
 * Iterator returned by SelectEntries to be used in a foreach loop
 */
class SelectIterator implements \Iterator, ActionInterface
{
    use SelectIteratorTrait;

    /**
     * @var RepositoryReadOnlyInterface
     */
    private $repository;

    /**
     * @var RepositorySelectQueryInterface|null
     */
    private $selectReference;

    public function __construct(RepositoryReadOnlyInterface $repository, array $query)
    {
        $this->repository = $repository;
        $this->query = $query;
    }
}
