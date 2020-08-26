<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Builder\InsertEntry;
use Squirrel\Entities\RepositoryWriteableInterface;

class InsertEntryTest extends \PHPUnit\Framework\TestCase
{
    private $repository;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryWriteableInterface::class);
    }

    public function testNoDataWrite()
    {
        $insertBuilder = new InsertEntry($this->repository);

        $this->repository
            ->shouldReceive('insert')
            ->once()
            ->with([]);

        $insertBuilder->write();

        $this->assertTrue(true);
    }

    public function testWrite()
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

    public function testWriteWithNewId()
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
