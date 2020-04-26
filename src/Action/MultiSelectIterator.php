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

    private MultiRepositoryReadOnlyInterface $source;
    private ?MultiRepositorySelectQueryInterface $selectReference = null;
    private ?array $lastResult = null;

    public function __construct(MultiRepositoryReadOnlyInterface $repository, array $query)
    {
        $this->source = $repository;
        $this->query = $query;
    }

    /**
     * @return array<string,mixed>
     */
    public function current(): array
    {
        // @codeCoverageIgnoreStart
        if ($this->lastResult === null) {
            throw new \LogicException('Cannot get current value if no result has been retrieved');
        }
        // @codeCoverageIgnoreEnd

        return $this->lastResult;
    }
}
