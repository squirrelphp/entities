<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Mockery\MockInterface;
use Squirrel\Entities\Builder\DeleteEntries;
use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

class DeleteEntriesTest extends \PHPUnit\Framework\TestCase
{
    /** @var RepositoryWriteableInterface&MockInterface  */
    private RepositoryWriteableInterface $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryWriteableInterface::class);
    }

    public function testNoDataWrite(): void
    {
        $deleteBuilder = new DeleteEntries($this->repository);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with([])
            ->andReturn(5);

        $deleteBuilder
            ->confirmNoWhereRestrictions()
            ->write();

        $this->assertTrue(true);
    }

    public function testWrite(): void
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

    public function testNoWhereNoConfirmation(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        $deleteBuilder = new DeleteEntries($this->repository);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with([])
            ->andReturn(5);

        $deleteBuilder->write();
    }
}
