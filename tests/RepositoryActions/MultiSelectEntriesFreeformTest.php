<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\MultiSelectEntriesFreeform;
use Squirrel\Entities\Action\MultiSelectIterator;
use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

class MultiSelectEntriesFreeformTest extends \PHPUnit\Framework\TestCase
{
    private $multiRepository;

    private $repository1;
    private $repository2;

    protected function setUp(): void
    {
        $this->multiRepository = \Mockery::mock(MultiRepositoryReadOnlyInterface::class);

        $this->repository1 = \Mockery::mock(RepositoryReadOnlyInterface::class);
        $this->repository2 = \Mockery::mock(RepositoryReadOnlyInterface::class);
    }

    public function testNoDataGetEntries()
    {
        $selectBuilder = new MultiSelectEntriesFreeform($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'fields' => [],
                'repositories' => [],
                'query' => '',
                'parameters' => [],
            ])
            ->andReturn([]);

        $results = $selectBuilder
            ->confirmFreeformQueriesAreNotRecommended('OK')
            ->getAllEntries();

        $this->assertEquals([], $results);
    }

    public function testGetEntries()
    {
        $selectBuilder = new MultiSelectEntriesFreeform($this->multiRepository);

        $selectBuilder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->fields([
                'id' => 'ticket.id',
            ])
            ->queryAfterFROM(':ticket:, :email: WHERE :ticket.id: = :email.id: AND :ticket.id: = ?')
            ->withParameters([
                5,
            ])
            ->confirmFreeformQueriesAreNotRecommended('OK');

        $this->multiRepository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'repositories' => [
                    'ticket' => $this->repository1,
                    'email' => $this->repository2,
                ],
                'fields' => [
                    'id' => 'ticket.id',
                ],
                'query' => ':ticket:, :email: WHERE :ticket.id: = :email.id: AND :ticket.id: = ?',
                'parameters' => [
                    5,
                ],
            ])
            ->andReturn([]);

        $results = $selectBuilder->getAllEntries();

        $this->assertEquals([], $results);
    }

    public function testGetOneEntry()
    {
        $selectBuilder = new MultiSelectEntriesFreeform($this->multiRepository);

        $selectBuilder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->field('ticket.id')
            ->queryAfterFROM(':ticket:, :email: WHERE :ticket.id: = :email.id: AND :ticket.id: = ?')
            ->withParameters([
                5,
            ])
            ->confirmFreeformQueriesAreNotRecommended('OK');

        $this->multiRepository
            ->shouldReceive('fetchOne')
            ->once()
            ->with([
                'repositories' => [
                    'ticket' => $this->repository1,
                    'email' => $this->repository2,
                ],
                'fields' => [
                    'ticket.id',
                ],
                'query' => ':ticket:, :email: WHERE :ticket.id: = :email.id: AND :ticket.id: = ?',
                'parameters' => [
                    5,
                ],
            ])
            ->andReturn([]);

        $results = $selectBuilder->getOneEntry();

        $this->assertEquals([], $results);
    }

    public function testNoDataGetFlattenedFields()
    {
        $selectBuilder = new MultiSelectEntriesFreeform($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('fetchAllAndFlatten')
            ->once()
            ->with([
                'fields' => [],
                'repositories' => [],
                'query' => '',
                'parameters' => [],
            ])
            ->andReturn([]);

        $results = $selectBuilder
            ->confirmFreeformQueriesAreNotRecommended('OK')
            ->getFlattenedFields();

        $this->assertEquals([], $results);
    }

    public function testIterator()
    {
        $selectBuilder = new MultiSelectEntriesFreeform($this->multiRepository);

        $expectedResult = new MultiSelectIterator($this->multiRepository, [
            'repositories' => [
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ],
            'fields' => [
                'ticket.id',
            ],
            'query' => ':ticket:, :email: WHERE :ticket.id: = :email.id: AND :ticket.id: = ?',
            'parameters' => [
                5,
            ],
        ]);

        $results = $selectBuilder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->field('ticket.id')
            ->queryAfterFROM(':ticket:, :email: WHERE :ticket.id: = :email.id: AND :ticket.id: = ?')
            ->withParameters([
                5,
            ])
            ->confirmFreeformQueriesAreNotRecommended('OK')
            ->getIterator();

        $this->assertEquals($expectedResult, $results);
    }

    public function testNoBadPracticeConfirmation()
    {
        $this->expectException(DBInvalidOptionException::class);

        $selectBuilder = new MultiSelectEntriesFreeform($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'fields' => [],
                'repositories' => [],
                'query' => '',
                'parameters' => [],
            ])
            ->andReturn([]);

        $selectBuilder->getAllEntries();
    }
}
