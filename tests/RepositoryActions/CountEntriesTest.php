<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Mockery\MockInterface;
use Squirrel\Entities\Builder\CountEntries;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class CountEntriesTest extends \PHPUnit\Framework\TestCase
{
    /** @var RepositoryReadOnlyInterface&MockInterface */
    private RepositoryReadOnlyInterface $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryReadOnlyInterface::class);
    }

    public function testNoDataGetEntries(): void
    {
        $selectBuilder = new CountEntries($this->repository);

        $this->repository
            ->shouldReceive('count')
            ->once()
            ->with([
                'where' => [],
                'lock' => false,
            ])
            ->andReturn(5);

        $results = $selectBuilder->getNumber();

        $this->assertEquals(5, $results);
    }

    public function testGetEntries(): void
    {
        $selectBuilder = new CountEntries($this->repository);

        $selectBuilder
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->blocking();

        $this->repository
            ->shouldReceive('count')
            ->once()
            ->with([
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'lock' => true,
            ])
            ->andReturn(55);

        $results = $selectBuilder->getNumber();

        $this->assertEquals(55, $results);
    }
}
