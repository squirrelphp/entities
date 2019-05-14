<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\UpdateEntries;
use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

class UpdateEntriesTest extends \PHPUnit\Framework\TestCase
{
    private $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryWriteableInterface::class);
    }

    public function testNoDataWrite()
    {
        $updateBuilder = new UpdateEntries($this->repository);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([], []);

        $updateBuilder
            ->confirmNoWhereRestrictions()
            ->write();

        $this->assertTrue(true);
    }

    public function testNoDataWriteAndReturnAffectedNumber()
    {
        $updateBuilder = new UpdateEntries($this->repository);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([], [])
            ->andReturn(89);

        $result = $updateBuilder
            ->confirmNoWhereRestrictions()
            ->writeAndReturnAffectedNumber();

        $this->assertEquals(89, $result);
    }

    public function testWrite()
    {
        $updateBuilder = new UpdateEntries($this->repository);

        $updateBuilder
            ->set([
                'dada' => 5,
                'fieldyField' => 'key',
            ])
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ]);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([
                'dada' => 5,
                'fieldyField' => 'key',
            ], [
                'responseId' => 5,
                'otherField' => '333',
            ]);

        $updateBuilder->write();

        $this->assertTrue(true);
    }

    public function testWriteAndReturnAffectedNumber()
    {
        $updateBuilder = new UpdateEntries($this->repository);

        $updateBuilder
            ->set([
                'dada' => 5,
                'fieldyField' => 'key',
            ])
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ]);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([
                'dada' => 5,
                'fieldyField' => 'key',
            ], [
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->andReturn(75);

        $result = $updateBuilder->writeAndReturnAffectedNumber();

        $this->assertEquals(75, $result);
    }

    public function testNoWhereNoConfirmation()
    {
        $this->expectException(DBInvalidOptionException::class);

        $updateBuilder = new UpdateEntries($this->repository);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([], []);

        $updateBuilder->write();
    }
}
