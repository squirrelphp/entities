<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\MultiRepositorySelectQueryInterface;

/**
 * Iterator returned by MultiSelectEntries to be used in a foreach loop
 */
class MultiSelectIterator implements \Iterator, ActionInterface
{
    /**
     * @var MultiRepositoryReadOnlyInterface
     */
    private $repository;

    /**
     * @var array SELECT query to execute
     */
    private $query = [];

    /**
     * @var MultiRepositorySelectQueryInterface|null
     */
    private $selectReference;

    /**
     * @var int
     */
    private $position = -1;

    /**
     * @var array|null
     */
    private $lastResult;

    public function __construct(MultiRepositoryReadOnlyInterface $repository, array $query)
    {
        $this->repository = $repository;
        $this->query = $query;
    }

    /**
     * @return array|null
     */
    public function current()
    {
        return $this->lastResult;
    }

    public function next()
    {
        if (isset($this->selectReference)) {
            $this->lastResult = $this->repository->fetch($this->selectReference);
            $this->position++;
        }
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return ( $this->lastResult === null ? false : true );
    }

    public function rewind()
    {
        $this->clear();

        $this->selectReference = $this->repository->select($this->query);

        $this->next();
    }

    public function clear()
    {
        if (isset($this->selectReference)) {
            $this->repository->clear($this->selectReference);
        }
        $this->position = -1;
        $this->selectReference = null;
        $this->lastResult = null;
    }
}
