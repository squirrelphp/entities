<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\SelectEntries;
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
            ->shouldReceive('select')
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

        $results = $selectBuilder->getEntries();

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
            ->shouldReceive('select')
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

        $results = $selectBuilder->getEntries();

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
            ->shouldReceive('select')
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

        $results = $selectBuilder->getEntries();

        $this->assertEquals([], $results);
    }

    public function testNoDataGetOneEntry()
    {
        $selectBuilder = new SelectEntries($this->repository);

        $this->repository
            ->shouldReceive('selectOne')
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
            ->shouldReceive('selectOne')
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
            ->shouldReceive('selectFlattenedFields')
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

        $results = $selectBuilder->getFlattenedFields();

        $this->assertEquals([], $results);
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
            ->shouldReceive('selectFlattenedFields')
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

        $results = $selectBuilder->getFlattenedFields();

        $this->assertEquals([], $results);
    }
}
