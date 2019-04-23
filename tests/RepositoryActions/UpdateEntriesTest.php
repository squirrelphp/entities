<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\UpdateEntries;
use Squirrel\Entities\RepositoryWriteableInterface;

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
            ->with([
                'changes' => [],
                'where' => [],
                'order' => [],
                'limit' => 0,
            ]);

        $updateBuilder->write();

        $this->assertTrue(true);
    }

    public function testNoDataWriteAndReturnAffectedNumber()
    {
        $updateBuilder = new UpdateEntries($this->repository);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([
                'changes' => [],
                'where' => [],
                'order' => [],
                'limit' => 0,
            ])
            ->andReturn(89);

        $result = $updateBuilder->writeAndReturnAffectedNumber();

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
            ])
            ->orderBy([
                'dada',
                'responseId',
            ])
            ->limitTo(6);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([
                'changes' => [
                    'dada' => 5,
                    'fieldyField' => 'key',
                ],
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'order' => [
                    'dada',
                    'responseId',
                ],
                'limit' => 6,
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
            ])
            ->orderBy([
                'dada',
                'responseId',
            ])
            ->limitTo(6);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([
                'changes' => [
                    'dada' => 5,
                    'fieldyField' => 'key',
                ],
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'order' => [
                    'dada',
                    'responseId',
                ],
                'limit' => 6,
            ])
            ->andReturn(75);

        $result = $updateBuilder->writeAndReturnAffectedNumber();

        $this->assertEquals(75, $result);
    }

    public function testWriteOrderByAsString()
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
            ])
            ->orderBy('responseId')
            ->limitTo(6);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with([
                'changes' => [
                    'dada' => 5,
                    'fieldyField' => 'key',
                ],
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'order' => [
                    'responseId',
                ],
                'limit' => 6,
            ]);

        $updateBuilder->write();

        $this->assertTrue(true);
    }
}
