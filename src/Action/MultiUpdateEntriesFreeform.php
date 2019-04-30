<?php

namespace Squirrel\Entities\Action;

use Squirrel\Entities\MultiRepositoryWriteableInterface;
use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\DBDebug;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Update query builder as a fluent object - build freeform query and execute it
 */
class MultiUpdateEntriesFreeform implements ActionInterface
{
    /**
     * @var MultiRepositoryWriteableInterface
     */
    private $queryHandler;

    /**
     * @var RepositoryWriteableInterface[] Repositories used in the multi query
     */
    private $repositories = [];

    /**
     * @var string Freeform query after FROM in a SELECT query
     */
    private $query = '';

    /**
     * @var array All query parameters within the SELECT query
     */
    private $parameters = [];

    /**
     * @var bool Confirmation that the programmer understands that freeform queries are seen as bad practice
     */
    private $confirmBadPractice = false;

    public function __construct(MultiRepositoryWriteableInterface $queryHandler)
    {
        $this->queryHandler = $queryHandler;
    }

    public function inRepositories(array $repositories): self
    {
        $this->repositories = $repositories;
        return $this;
    }

    public function query(string $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function withParameters(array $queryParameters = []): self
    {
        $this->parameters = $queryParameters;
        return $this;
    }

    public function confirmFreeformQueriesAreBadPractice(string $confirmWithOK): self
    {
        if ($confirmWithOK === 'OK') {
            $this->confirmBadPractice = true;
        }
        return $this;
    }

    /**
     * Write changes to database
     */
    public function write(): void
    {
        $this->writeAndReturnAffectedNumber();
    }

    /**
     * Write changes to database and return affected entries number
     *
     * @return int Number of affected entries in database
     */
    public function writeAndReturnAffectedNumber(): int
    {
        $this->makeSureBadPracticeWasConfirmed();

        return $this->queryHandler->update([
            'repositories' => $this->repositories,
            'query' => $this->query,
            'parameters' => $this->parameters,
        ]);
    }

    private function makeSureBadPracticeWasConfirmed()
    {
        if ($this->confirmBadPractice !== true) {
            throw DBDebug::createException(
                DBInvalidOptionException::class,
                [ActionInterface::class],
                'No confirmation that freeform queries are bad practice'
            );
        }
    }
}
