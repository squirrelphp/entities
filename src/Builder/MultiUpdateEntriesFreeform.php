<?php

namespace Squirrel\Entities\Builder;

use Squirrel\Debug\Debug;
use Squirrel\Entities\MultiRepositoryWriteableInterface;
use Squirrel\Entities\RepositoryBuilderWriteableInterface;
use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\Builder\BuilderInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Update query builder as a fluent object - build freeform query and execute it
 */
class MultiUpdateEntriesFreeform implements BuilderInterface
{
    /**
     * @var array<string,RepositoryBuilderWriteableInterface|RepositoryWriteableInterface> Repositories used in the multi query
     */
    private array $repositories = [];

    /**
     * @var string Freeform query after FROM in a SELECT query
     */
    private string $query = '';

    /**
     * @var array<int,mixed> All query parameters within the SELECT query
     */
    private array $parameters = [];

    /**
     * @var bool Confirmation that the programmer understands that freeform queries are seen as bad practice
     */
    private bool $confirmBadPractice = false;

    public function __construct(
        private MultiRepositoryWriteableInterface $queryHandler,
    ) {
    }

    /**
     * @param array<string,RepositoryBuilderWriteableInterface|RepositoryWriteableInterface> $repositories
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
            $this->parameters,
        );
    }

    private function makeSureBadPracticeWasConfirmed(): void
    {
        if ($this->confirmBadPractice !== true) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                'No confirmation that freeform queries are bad practice - ' .
                'call "confirmFreeformQueriesAreNotRecommended" with "OK" to confirm the freeform query',
                ignoreClasses: [BuilderInterface::class],
            );
        }
    }
}
