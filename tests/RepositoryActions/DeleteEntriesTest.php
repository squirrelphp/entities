<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\DeleteEntries;
use Squirrel\Entities\RepositoryWriteableInterface;

class DeleteEntriesTest extends \PHPUnit\Framework\TestCase
{
    private $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryWriteableInterface::class);
    }

    public function testNoDataWrite()
    {
        $deleteBuilder = new DeleteEntries($this->repository);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with([])
            ->andReturn(5);

        $deleteBuilder->write();

        $this->assertTrue(true);
    }

    public function testWrite()
    {
        $deleteBuilder = new DeleteEntries($this->repository);

        $deleteBuilder
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ]);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->andReturn(55);

        $results = $deleteBuilder->writeAndReturnAffectedNumber();

        $this->assertEquals(55, $results);
    }
}
