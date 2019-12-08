<?php

namespace Squirrel\Entities\Action;

use Squirrel\Debug\Debug;
use Squirrel\Entities\MultiRepositoryWriteableInterface;
use Squirrel\Entities\RepositoryWriteableInterface;
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
     * @var array<int,mixed> All query parameters within the SELECT query
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

    /**
     * @param RepositoryWriteableInterface[] $repositories
     */
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

    /**
     * @param array<int,mixed> $queryParameters
     */
    public function withParameters(array $queryParameters = []): self
    {
        $this->parameters = $queryParameters;
        return $this;
    }

    public function confirmFreeformQueriesAreNotRecommended(string $confirmWithOK): self
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

        return $this->queryHandler->update(
            $this->repositories,
            $this->query,
            $this->parameters
        );
    }

    private function makeSureBadPracticeWasConfirmed(): void
    {
        if ($this->confirmBadPractice !== true) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [ActionInterface::class],
                'No confirmation that freeform queries are bad practice - ' .
                'call "confirmFreeformQueriesAreNotRecommended" with "OK" to confirm the freeform query'
            );
        }
    }
}
