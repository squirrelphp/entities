<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Mockery\MockInterface;
use Squirrel\Entities\Builder\InsertOrUpdateEntry;
use Squirrel\Entities\RepositoryWriteableInterface;

class InsertOrUpdateEntryTest extends \PHPUnit\Framework\TestCase
{
    /** @var RepositoryWriteableInterface&MockInterface  */
    private RepositoryWriteableInterface $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryWriteableInterface::class);
    }

    public function testNoDataWrite(): void
    {
        $insertBuilder = new InsertOrUpdateEntry($this->repository);

        $this->repository
            ->shouldReceive('insertOrUpdate')
            ->once()
            ->with([], [], []);

        $insertBuilder->write();

        $this->assertTrue(true);
    }

    public function testNoDataWriteAndReturnWhatHappened(): void
    {
        $insertBuilder = new InsertOrUpdateEntry($this->repository);

        $this->repository
            ->shouldReceive('insertOrUpdate')
            ->once()
            ->with([], [], [])
            ->andReturn('insert');

        $insertBuilder->write();

        $this->assertTrue(true);
    }

    public function testWrite(): void
    {
        $insertBuilder = new InsertOrUpdateEntry($this->repository);

        $insertBuilder
            ->set([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->index([
                'responseId',
            ]);

        $this->repository
            ->shouldReceive('insertOrUpdate')
            ->once()
            ->with([
                'responseId' => 5,
                'otherField' => '333',
            ], [
                'responseId',
            ], null);

        $insertBuilder->write();

        $this->assertTrue(true);
    }

    public function testWriteIndexIsString(): void
    {
        $insertBuilder = new InsertOrUpdateEntry($this->repository);

        $insertBuilder
            ->set([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->index('responseId');

        $this->repository
            ->shouldReceive('insertOrUpdate')
            ->once()
            ->with([
                'responseId' => 5,
                'otherField' => '333',
            ], [
                'responseId',
            ], null);

        $insertBuilder->write();

        $this->assertTrue(true);
    }

    public function testWriteSetUpdates(): void
    {
        $insertBuilder = new InsertOrUpdateEntry($this->repository);

        $insertBuilder
            ->set([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->index([
                'responseId',
            ])
            ->setOnUpdate([
                'otherField' => '66',
                'ladida' => true,
            ]);

        $this->repository
            ->shouldReceive('insertOrUpdate')
            ->once()
            ->with([
                'responseId' => 5,
                'otherField' => '333',
            ], [
                'responseId',
            ], [
                'otherField' => '66',
                'ladida' => true,
            ]);

        $insertBuilder->write();

        $this->assertTrue(true);
    }

    public function testWriteSetUpdatesAsString(): void
    {
        $insertBuilder = new InsertOrUpdateEntry($this->repository);

        $insertBuilder
            ->set([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->index([
                'responseId',
            ])
            ->setOnUpdate(':otherField: = :otherField: + 1');

        $this->repository
            ->shouldReceive('insertOrUpdate')
            ->once()
            ->with([
                'responseId' => 5,
                'otherField' => '333',
            ], [
                'responseId',
            ], [
                ':otherField: = :otherField: + 1',
            ]);

        $insertBuilder->write();

        $this->assertTrue(true);
    }
}
