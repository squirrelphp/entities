<?php

namespace Squirrel\Entities\Action;

use Squirrel\Debug\Debug;
use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\RepositoryBuilderReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Select query builder as a fluent object - build query and return entries or flattened fields
 *
 * @implements \IteratorAggregate<int,array<string,mixed>>
 */
class MultiSelectEntriesFreeform implements ActionInterface, \IteratorAggregate
{
    /**
     * @var MultiRepositoryReadOnlyInterface
     */
    private $queryHandler;

    /**
     * @var array<int|string,string> Only retrieve these fields of the repositories
     */
    private $fields = [];

    /**
     * @var array<string,RepositoryBuilderReadOnlyInterface|RepositoryReadOnlyInterface> Repositories used in the multi query
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

    public function __construct(MultiRepositoryReadOnlyInterface $queryHandler)
    {
        $this->queryHandler = $queryHandler;
    }

    public function field(string $getThisField): self
    {
        $this->fields = [$getThisField];
        return $this;
    }

    /**
     * @param array<int|string,string> $getTheseFields
     */
    public function fields(array $getTheseFields): self
    {
        $this->fields = $getTheseFields;
        return $this;
    }

    /**
     * @param array<string,RepositoryBuilderReadOnlyInterface|RepositoryReadOnlyInterface> $repositories
     */
    public function inRepositories(array $repositories): self
    {
        $this->repositories = $repositories;
        return $this;
    }

    public function queryAfterFROM(string $query): self
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
     * Execute SELECT query and return a list of entries as arrays that matched it
     *
     * @return array<int,array<string,mixed>>
     */
    public function getAllEntries(): array
    {
        $this->makeSureBadPracticeWasConfirmed();

        return $this->queryHandler->fetchAll([
            'fields' => $this->fields,
            'repositories' => $this->repositories,
            'query' => $this->query,
            'parameters' => $this->parameters,
        ]);
    }

    /**
     * Execute SELECT query and return exactly one entry, if one was found at all
     *
     * @return array<string,mixed>|null
     */
    public function getOneEntry(): ?array
    {
        $this->makeSureBadPracticeWasConfirmed();

        return $this->queryHandler->fetchOne([
            'fields' => $this->fields,
            'repositories' => $this->repositories,
            'query' => $this->query,
            'parameters' => $this->parameters,
        ]);
    }

    /**
     * Execute SELECT query and return the fields as a list of values
     *
     * @return array<bool|int|float|string|null>
     */
    public function getFlattenedFields(): array
    {
        $this->makeSureBadPracticeWasConfirmed();

        return $this->queryHandler->fetchAll([
            'fields' => $this->fields,
            'repositories' => $this->repositories,
            'query' => $this->query,
            'parameters' => $this->parameters,
            'flattenFields' => true,
        ]);
    }

    public function getIterator(): MultiSelectIterator
    {
        $this->makeSureBadPracticeWasConfirmed();

        return new MultiSelectIterator($this->queryHandler, [
            'fields' => $this->fields,
            'repositories' => $this->repositories,
            'query' => $this->query,
            'parameters' => $this->parameters,
        ]);
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
