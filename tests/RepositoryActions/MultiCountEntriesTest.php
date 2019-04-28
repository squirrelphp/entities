<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\MultiCountEntries;
use Squirrel\Entities\MultiRepositoryReadOnlyInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

class MultiCountEntriesTest extends \PHPUnit\Framework\TestCase
{
    private $multiRepository;

    private $repository1;
    private $repository2;

    protected function setUp(): void
    {
        $this->multiRepository = \Mockery::mock(MultiRepositoryReadOnlyInterface::class);

        $this->repository1 = \Mockery::mock(RepositoryReadOnlyInterface::class);
        $this->repository2 = \Mockery::mock(RepositoryReadOnlyInterface::class);
    }

    public function testNoDataGetEntries()
    {
        $builder = new MultiCountEntries($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('count')
            ->once()
            ->with([
                'repositories' => [],
                'tables' => [],
                'where' => [],
                'lock' => false,
            ])
            ->andReturn(8);

        $results = $builder->getNumber();

        $this->assertEquals(8, $results);
    }

    public function testGetEntries()
    {
        $builder = new MultiCountEntries($this->multiRepository);

        $builder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->joinTables([
                'ticket',
                'email',
            ])
            ->where([
                'responseId' => 5,
                'otherField' => '333',
            ])
            ->blocking();

        $this->multiRepository
            ->shouldReceive('count')
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
                'where' => [
                    'responseId' => 5,
                    'otherField' => '333',
                ],
                'lock' => true,
            ])
            ->andReturn(565);

        $results = $builder->getNumber();

        $this->assertEquals(565, $results);
    }
}
