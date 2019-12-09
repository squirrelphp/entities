<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\MultiRepositorySelectQueryInterface;
use Squirrel\Queries\Builder\SelectIteratorTrait;

/**
 * Iterator returned by MultiSelectEntries to be used in a foreach loop
 *
 * @implements \Iterator<int,array<string,mixed>>
 */
class MultiSelectIterator implements \Iterator, ActionInterface
{
    use SelectIteratorTrait;

    /**
     * @var MultiRepositoryReadOnlyInterface
     */
    private $source;

    /**
     * @var MultiRepositorySelectQueryInterface|null
     */
    private $selectReference;

    public function __construct(MultiRepositoryReadOnlyInterface $repository, array $query)
    {
        $this->source = $repository;
        $this->query = $query;
    }
}
