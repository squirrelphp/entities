<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Mockery\MockInterface;
use Squirrel\Entities\Builder\InsertEntry;
use Squirrel\Entities\RepositoryWriteableInterface;

class InsertEntryTest extends \PHPUnit\Framework\TestCase
{
    /** @var RepositoryWriteableInterface&MockInterface  */
    private RepositoryWriteableInterface $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryWriteableInterface::class);
    }

    public function testNoDataWrite(): void
    {
        $insertBuilder = new InsertEntry($this->repository);

        $this->repository
            ->shouldReceive('insert')
            ->once()
            ->with([]);

        $insertBuilder->write();

        $this->assertTrue(true);
    }

    public function testWrite(): void
    {
        $insertBuilder = new InsertEntry($this->repository);

        $insertBuilder
            ->set([
                'responseId' => 5,
                'otherField' => '333',
            ]);

        $this->repository
            ->shouldReceive('insert')
            ->once()
            ->with([
                'responseId' => 5,
                'otherField' => '333',
            ]);

        $insertBuilder->write();

        $this->assertTrue(true);
    }

    public function testWriteWithNewId(): void
    {
        $insertBuilder = new InsertEntry($this->repository);

        $insertBuilder
            ->set([
                'responseId' => 5,
                'otherField' => '333',
            ]);

        $this->repository
            ->shouldReceive('insert')
            ->once()
            ->with([
                'responseId' => 5,
                'otherField' => '333',
            ], true)
            ->andReturn(54);

        $insertId = $insertBuilder->writeAndReturnNewId();

        $this->assertEquals(54, $insertId);
    }
}
