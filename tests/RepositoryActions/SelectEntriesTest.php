<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Mockery\MockInterface;
use Squirrel\Entities\Builder\SelectEntries;
use Squirrel\Entities\Builder\SelectIterator;
use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

class SelectEntriesTest extends \PHPUnit\Framework\TestCase
{
    /** @var RepositoryReadOnlyInterface&MockInterface */
    private RepositoryReadOnlyInterface $repository;
    private SelectEntries $selectBuilder;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryReadOnlyInterface::class);
        $this->selectBuilder = new SelectEntries($this->repository);
    }

    private function getSelector(array $query): SelectIterator
    {
        return new SelectIterator($this->repository, $query);
    }

    public function testNoDataGetEntries(): void
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

    public function testGetEntries(): void
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

    public function testGetEntriesFieldAndStringOrder(): void
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

    public function testNoDataGetOneEntry(): void
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

    public function testGetOneEntry(): void
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

    public function testNoDataGetFlattenedFields(): void
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

    public function testIterator(): void
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

    public function testGetFlattenedFields(): void
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

    public function testGetFlattenedIntegerFields(): void
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
            ->andReturn([5, 6, 8, '33', '64']);

        $results = $this->selectBuilder->getFlattenedIntegerFields();

        $this->assertEquals([5, 6, 8, 33, 64], $results);
    }

    public function testGetFlattenedFloatFields(): void
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
            ->andReturn([5, '6', 8, 3.7, '4.6']);

        $results = $this->selectBuilder->getFlattenedFloatFields();

        $this->assertEquals([5.0, 6.0, 8.0, 3.7, 4.6], $results);
    }

    public function testGetFlattenedBooleanFields(): void
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
            ->andReturn([true, false, true, true, false, 0, 1, '0', '1']);

        $results = $this->selectBuilder->getFlattenedBooleanFields();

        $this->assertEquals([true, false, true, true, false, false, true, false, true], $results);
    }

    public function testGetFlattenedStringFields(): void
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
            ->andReturn(['dada', 5, 'rtew', '', 7777.3]);

        $results = $this->selectBuilder->getFlattenedStringFields();

        $this->assertEquals(['dada', '5', 'rtew', '', '7777.3'], $results);
    }

    public function testGetFlattenedIntegerFieldsWrongScalarType(): void
    {
        $this->expectException(DBInvalidOptionException::class);

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
            ->andReturn([5, '5lada', 6, 8]);

        $this->selectBuilder->getFlattenedIntegerFields();
    }

    public function testGetFlattenedIntegerFieldsWrongNonScalarType(): void
    {
        $this->expectException(DBInvalidOptionException::class);

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
            ->andReturn([5, true, 6, 8]);

        $this->selectBuilder->getFlattenedIntegerFields();
    }

    public function testGetFlattenedFloatFieldsWrongScalarType(): void
    {
        $this->expectException(DBInvalidOptionException::class);

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
            ->andReturn([5, 6, 8, '3.7nonnumber']);

        $this->selectBuilder->getFlattenedFloatFields();
    }

    public function testGetFlattenedFloatFieldsWrongNonScalarType(): void
    {
        $this->expectException(DBInvalidOptionException::class);

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
            ->andReturn([5, 6, 8, true]);

        $this->selectBuilder->getFlattenedFloatFields();
    }

    public function testGetFlattenedBooleanFieldsWrongType(): void
    {
        $this->expectException(DBInvalidOptionException::class);

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
            ->andReturn([true, false, true, 'dada', false]);

        $this->selectBuilder->getFlattenedBooleanFields();
    }

    public function testGetFlattenedStringFieldsWrongType(): void
    {
        $this->expectException(DBInvalidOptionException::class);

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
            ->andReturn(['dada', '5', 'rtew', false, '7777.3']);

        $this->selectBuilder->getFlattenedStringFields();
    }
}
