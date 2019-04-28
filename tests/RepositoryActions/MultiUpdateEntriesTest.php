<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\MultiUpdateEntries;
use Squirrel\Entities\MultiRepositoryWriteableInterface;
use Squirrel\Entities\RepositoryWriteableInterface;

class MultiUpdateEntriesTest extends \PHPUnit\Framework\TestCase
{
    private $multiRepository;

    private $repository1;
    private $repository2;

    protected function setUp(): void
    {
        $this->multiRepository = \Mockery::mock(MultiRepositoryWriteableInterface::class);

        $this->repository1 = \Mockery::mock(RepositoryWriteableInterface::class);
        $this->repository2 = \Mockery::mock(RepositoryWriteableInterface::class);
    }

    public function testNoDataGetEntries()
    {
        $builder = new MultiUpdateEntries($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('update')
            ->once()
            ->with([
                'repositories' => [],
                'tables' => [],
                'changes' => [],
                'where' => [],
                'order' => [],
                'limit' => 0,
            ])
            ->andReturn(8);

        $results = $builder->writeAndReturnAffectedNumber();

        $this->assertEquals(8, $results);
    }

    public function testGetEntries()
    {
        $builder = new MultiUpdateEntries($this->multiRepository);

        $builder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->joinTables([
                'ticket',
                'email',
            ])
            ->set([
                'firstName' => 'Jane',
            ])
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->orderBy([
                'responseId',
            ])
            ->limitTo(33);

        $this->multiRepository
            ->shouldReceive('update')
            ->once()
            ->with([
                'repositories' => [
                    'ticket' => $this->repository1,
                    'email' => $this->repository2,
                ],
                'tables' => [
                    'ticket',
                    'email',
                ],
                'changes' => [
                    'firstName' => 'Jane',
                ],
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'order' => [
                    'responseId',
                ],
                'limit' => 33,
            ])
            ->andReturn(565);

        $results = $builder->writeAndReturnAffectedNumber();

        $this->assertEquals(565, $results);
    }

    public function testGetEntriesSingular()
    {
        $builder = new MultiUpdateEntries($this->multiRepository);

        $builder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->joinTables([
                'ticket',
                'email',
            ])
            ->set([
                'firstName' => 'Jane',
            ])
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->orderBy('responseId')
            ->limitTo(33);

        $this->multiRepository
            ->shouldReceive('update')
            ->once()
            ->with([
                'repositories' => [
                    'ticket' => $this->repository1,
                    'email' => $this->repository2,
                ],
                'tables' => [
                    'ticket',
                    'email',
                ],
                'changes' => [
                    'firstName' => 'Jane',
                ],
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'order' => [
                    'responseId',
                ],
                'limit' => 33,
            ]);

        $builder->write();

        $this->assertTrue(true);
    }
}
