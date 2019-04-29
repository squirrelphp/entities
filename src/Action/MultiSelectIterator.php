<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\MultiRepositorySelectQueryInterface;

/**
 * Iterator returned by MultiSelectEntries to be used in a foreach loop
 */
class MultiSelectIterator implements \Iterator, ActionInterface
{
    use SelectIteratorTrait;

    /**
     * @var MultiRepositoryReadOnlyInterface
     */
    private $repository;

    /**
     * @var MultiRepositorySelectQueryInterface|null
     */
    private $selectReference;

    public function __construct(MultiRepositoryReadOnlyInterface $repository, array $query)
    {
        $this->repository = $repository;
        $this->query = $query;
    }
}
