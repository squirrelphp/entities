<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Mockery\MockInterface;
use Squirrel\Entities\Builder\MultiSelectEntries;
use Squirrel\Entities\Builder\MultiSelectIterator;
use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class MultiSelectEntriesTest extends \PHPUnit\Framework\TestCase
{
    /** @var MultiRepositoryReadOnlyInterface&MockInterface  */
    private MultiRepositoryReadOnlyInterface $multiRepository;
    /** @var RepositoryReadOnlyInterface&MockInterface */
    private RepositoryReadOnlyInterface $repository1;
    /** @var RepositoryReadOnlyInterface&MockInterface */
    private RepositoryReadOnlyInterface $repository2;

    protected function setUp(): void
    {
        $this->multiRepository = \Mockery::mock(MultiRepositoryReadOnlyInterface::class);

        $this->repository1 = \Mockery::mock(RepositoryReadOnlyInterface::class);
        $this->repository2 = \Mockery::mock(RepositoryReadOnlyInterface::class);
    }

    public function testNoDataGetEntries(): void
    {
        $selectBuilder = new MultiSelectEntries($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'fields' => [],
                'repositories' => [],
                'tables' => [],
                'where' => [],
                'order' => [],
                'group' => [],
                'limit' => 0,
                'offset' => 0,
                'lock' => false,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getAllEntries();

        $this->assertEquals([], $results);
    }

    public function testGetEntries(): void
    {
        $selectBuilder = new MultiSelectEntries($this->multiRepository);

        $selectBuilder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
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

        $this->multiRepository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'repositories' => [
                    'ticket' => $this->repository1,
                    'email' => $this->repository2,
                ],
                'tables' => [],
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'group' => [],
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

    public function testGetEntriesFieldAndStringOrder(): void
    {
        $selectBuilder = new MultiSelectEntries($this->multiRepository);

        $selectBuilder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->groupBy('otherField')
            ->orderBy('responseId')
            ->startAt(13)
            ->limitTo(45)
            ->blocking()
            ->field('responseId');

        $this->multiRepository
            ->shouldReceive('fetchAll')
            ->once()
            ->with([
                'repositories' => [
                    'ticket' => $this->repository1,
                    'email' => $this->repository2,
                ],
                'tables' => [],
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'group' => [
                    'otherField',
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

    public function testNoDataGetOneEntry(): void
    {
        $selectBuilder = new MultiSelectEntries($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('fetchOne')
            ->once()
            ->with([
                'fields' => [],
                'repositories' => [],
                'tables' => [],
                'where' => [],
                'order' => [],
                'group' => [],
                'offset' => 0,
                'lock' => false,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getOneEntry();

        $this->assertEquals([], $results);
    }

    public function testGetOneEntry(): void
    {
        $selectBuilder = new MultiSelectEntries($this->multiRepository);

        $selectBuilder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->groupBy([
                'responseId',
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

        $this->multiRepository
            ->shouldReceive('fetchOne')
            ->once()
            ->with([
                'repositories' => [
                    'ticket' => $this->repository1,
                    'email' => $this->repository2,
                ],
                'tables' => [],
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'group' => [
                    'responseId',
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

    public function testNoDataGetFlattenedFields(): void
    {
        $selectBuilder = new MultiSelectEntries($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('fetchAllAndFlatten')
            ->once()
            ->with([
                'fields' => [],
                'repositories' => [],
                'tables' => [],
                'where' => [],
                'order' => [],
                'group' => [],
                'limit' => 0,
                'offset' => 0,
                'lock' => false,
            ])
            ->andReturn([]);

        $results = $selectBuilder->getFlattenedFields();

        $this->assertEquals([], $results);
    }

    public function testIterator(): void
    {
        $selectBuilder = new MultiSelectEntries($this->multiRepository);

        $expectedResult = new MultiSelectIterator($this->multiRepository, [
            'repositories' => [
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ],
            'tables' => [
                'ticket',
                'email',
            ],
            'where' => [
                'responseId' => 5,
                'otherField' => '333',
            ],
            'group' => [],
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
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->joinTables([
                'ticket',
                'email',
            ])
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
        $selectBuilder = new MultiSelectEntries($this->multiRepository);

        $selectBuilder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
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

        $this->multiRepository
            ->shouldReceive('fetchAllAndFlatten')
            ->once()
            ->with([
                'repositories' => [
                    'ticket' => $this->repository1,
                    'email' => $this->repository2,
                ],
                'tables' => [],
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'group' => [],
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
