<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Builder\InsertOrUpdateEntry;
use Squirrel\Entities\RepositoryWriteableInterface;

class InsertOrUpdateEntryTest extends \PHPUnit\Framework\TestCase
{
    private $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryWriteableInterface::class);
    }

    public function testNoDataWrite()
    {
        $insertBuilder = new InsertOrUpdateEntry($this->repository);

        $this->repository
            ->shouldReceive('insertOrUpdate')
            ->once()
            ->with([], [], []);

        $insertBuilder->write();

        $this->assertTrue(true);
    }

    public function testNoDataWriteAndReturnWhatHappened()
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

    public function testWrite()
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

    public function testWriteIndexIsString()
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

    public function testWriteSetUpdates()
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

    public function testWriteSetUpdatesAsString()
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
