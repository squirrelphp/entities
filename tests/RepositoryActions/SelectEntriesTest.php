<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\SelectEntries;
use Squirrel\Entities\Action\SelectIterator;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class SelectEntriesTest extends \PHPUnit\Framework\TestCase
{
    private $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryReadOnlyInterface::class);
    }

    public function testNoDataGetEntries()
    {
        $selectBuilder = new SelectEntries($this->repository);

        $this->repository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'where' => [],
                'order' => [],
                'fields' => [],
                'limit' => 0,
                'offset' => 0,
                'lock' => false,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getAllEntries();

        $this->assertEquals([], $results);
    }

    public function testGetEntries()
    {
        $selectBuilder = new SelectEntries($this->repository);

        $selectBuilder
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->orderBy([
                'responseId' => 'DESC',
            ])
            ->startAt(13)
            ->limitTo(45)
            ->blocking()
            ->fields([
                'responseId',
                'otherField',
            ]);

        $this->repository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'order' => [
                    'responseId' => 'DESC',
                ],
                'fields' => [
                    'responseId',
                    'otherField',
                ],
                'limit' => 45,
                'offset' => 13,
                'lock' => true,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getAllEntries();

        $this->assertEquals([], $results);
    }

    public function testGetEntriesFieldAndStringOrder()
    {
        $selectBuilder = new SelectEntries($this->repository);

        $selectBuilder
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->orderBy('responseId')
            ->startAt(13)
            ->limitTo(45)
            ->blocking()
            ->field('responseId');

        $this->repository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'order' => [
                    'responseId',
                ],
                'fields' => [
                    'responseId',
                ],
                'limit' => 45,
                'offset' => 13,
                'lock' => true,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getAllEntries();

        $this->assertEquals([], $results);
    }

    public function testNoDataGetOneEntry()
    {
        $selectBuilder = new SelectEntries($this->repository);

        $this->repository
            ->shouldReceive('fetchOne')
            ->once()
            ->with([
                'where' => [],
                'order' => [],
                'fields' => [],
                'offset' => 0,
                'lock' => false,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getOneEntry();

        $this->assertEquals([], $results);
    }

    public function testGetOneEntry()
    {
        $selectBuilder = new SelectEntries($this->repository);

        $selectBuilder
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->orderBy([
                'responseId' => 'DESC',
            ])
            ->startAt(13)
            ->blocking()
            ->fields([
                'responseId',
                'otherField',
            ]);

        $this->repository
            ->shouldReceive('fetchOne')
            ->once()
            ->with([
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'order' => [
                    'responseId' => 'DESC',
                ],
                'fields' => [
                    'responseId',
                    'otherField',
                ],
                'offset' => 13,
                'lock' => true,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getOneEntry();

        $this->assertEquals([], $results);
    }

    public function testNoDataGetFlattenedFields()
    {
        $selectBuilder = new SelectEntries($this->repository);

        $this->repository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'where' => [],
                'order' => [],
                'fields' => [],
                'limit' => 0,
                'offset' => 0,
                'lock' => false,
                'flattenFields' => true,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getFlattenedFields();

        $this->assertEquals([], $results);
    }

    public function testIterator()
    {
        $selectBuilder = new SelectEntries($this->repository);

        $expectedResult = new SelectIterator($this->repository, [
            'where' => [
                'responseId' => 5,
                'otherField' => '333',
            ],
            'order' => [
                'responseId' => 'DESC',
            ],
            'fields' => [
                'responseId',
                'otherField',
            ],
            'limit' => 55,
            'offset' => 13,
            'lock' => true,
        ]);

        $results = $selectBuilder
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->orderBy([
                'responseId' => 'DESC',
            ])
            ->startAt(13)
            ->limitTo(55)
            ->blocking()
            ->fields([
                'responseId',
                'otherField',
            ])
            ->getIterator();

        $this->assertEquals($expectedResult, $results);
    }

    public function testGetFlattenedFields()
    {
        $selectBuilder = new SelectEntries($this->repository);

        $selectBuilder
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->orderBy([
                'responseId' => 'DESC',
            ])
            ->startAt(13)
            ->limitTo(45)
            ->blocking()
            ->fields([
                'responseId',
                'otherField',
            ]);

        $this->repository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'order' => [
                    'responseId' => 'DESC',
                ],
                'fields' => [
                    'responseId',
                    'otherField',
                ],
                'limit' => 45,
                'offset' => 13,
                'lock' => true,
                'flattenFields' => true,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getFlattenedFields();

        $this->assertEquals([], $results);
    }
}
