<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\CountEntries;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class CountEntriesTest extends \PHPUnit\Framework\TestCase
{
    private $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryReadOnlyInterface::class);
    }

    public function testNoDataGetEntries()
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

    public function testGetEntries()
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
