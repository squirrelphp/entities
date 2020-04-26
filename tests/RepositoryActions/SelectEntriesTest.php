<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\SelectEntries;
use Squirrel\Entities\Action\SelectIterator;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class SelectEntriesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RepositoryReadOnlyInterface
     */
    private $repository;

    /**
     * @var SelectEntries
     */
    private $selectBuilder;

    /**
     * @var SelectIterator
     */
    private $selectIteratorClass;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryReadOnlyInterface::class);
        $this->selectBuilder = new SelectEntries($this->repository);
        $this->selectIteratorClass = SelectIterator::class;
    }

    private function getSelector($query)
    {
        $iteratorClass = $this->selectIteratorClass;
        return new $iteratorClass($this->repository, $query);
    }

    public function testNoDataGetEntries()
    {
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

        $results = $this->selectBuilder->getAllEntries();

        $this->assertEquals([], $results);
    }

    public function testGetEntries()
    {
        $this->selectBuilder
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

        $results = $this->selectBuilder->getAllEntries();

        $this->assertEquals([], $results);
    }

    public function testGetEntriesFieldAndStringOrder()
    {
        $this->selectBuilder
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

        $results = $this->selectBuilder->getAllEntries();

        $this->assertEquals([], $results);
    }

    public function testNoDataGetOneEntry()
    {
        $expected = new \stdClass();

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
            ->andReturn($expected);

        $results = $this->selectBuilder->getOneEntry();

        $this->assertEquals($expected, $results);
    }

    public function testGetOneEntry()
    {
        $expected = new \stdClass();

        $this->selectBuilder
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
            ->andReturn($expected);

        $results = $this->selectBuilder->getOneEntry();

        $this->assertEquals($expected, $results);
    }

    public function testNoDataGetFlattenedFields()
    {
        $this->repository
            ->shouldReceive('fetchAllAndFlatten')
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

        $results = $this->selectBuilder->getFlattenedFields();

        $this->assertEquals([], $results);
    }

    public function testIterator()
    {
        $expectedResult = $this->getSelector([
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

        $results = $this->selectBuilder
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
        $this->selectBuilder
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
            ->shouldReceive('fetchAllAndFlatten')
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

        $results = $this->selectBuilder->getFlattenedFields();

        $this->assertEquals([], $results);
    }
}
