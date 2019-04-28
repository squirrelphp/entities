<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\SelectIterator;
use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Entities\RepositorySelectQueryInterface;

class SelectIteratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RepositoryReadOnlyInterface
     */
    private $repository;

    /**
     * @var array
     */
    private $query;

    protected function setUp(): void
    {
        $this->repository = \Mockery::mock(RepositoryReadOnlyInterface::class);
        $this->query = [
            'tables' => [
                'table6',
                'otherTable g LEFT JOIN superTable e ON (g.id = e.id AND g.name=?)' => 'Jane',
            ],
            'where' => [
                'g.somefield' => 33,
            ],
            'group' => [
                'g.somefield',
            ],
            'order' => [
                'e.id',
            ],
            'fields' => [
                'field1',
                'field6' => 'somefield',
            ],
            'limit' => 55,
            'offset' => 13,
            'lock' => true,
        ];
    }

    public function testLoop()
    {
        $selectQuery = \Mockery::mock(RepositorySelectQueryInterface::class);

        $this->repository
            ->shouldReceive('select')
            ->once()
            ->with($this->query)
            ->andReturn($selectQuery);

        $this->repository
            ->shouldReceive('fetch')
            ->once()
            ->with($selectQuery)
            ->andReturn(['dada' => 55, 'other' => 'Jane']);

        $this->repository
            ->shouldReceive('fetch')
            ->once()
            ->with($selectQuery)
            ->andReturn(['dada' => 5888, 'other' => 'Henry']);

        $this->repository
            ->shouldReceive('fetch')
            ->once()
            ->with($selectQuery)
            ->andReturn(null);

        $this->repository
            ->shouldReceive('clear')
            ->once()
            ->with($selectQuery);

        $iterator = new SelectIterator($this->repository, $this->query);

        $assertionsCount = 0;

        foreach ($iterator as $key => $entry) {
            if ($key === 0) {
                $this->assertEquals(['dada' => 55, 'other' => 'Jane'], $entry);
                $assertionsCount++;
            } elseif ($key === 1) {
                $this->assertEquals(['dada' => 5888, 'other' => 'Henry'], $entry);
                $assertionsCount++;
            } else {
                $this->assertTrue(false);
            }
        }

        $iterator->clear();

        $this->assertEquals(2, $assertionsCount);

        $this->repository
            ->shouldReceive('select')
            ->once()
            ->with($this->query)
            ->andReturn($selectQuery);

        $this->repository
            ->shouldReceive('fetch')
            ->once()
            ->with($selectQuery)
            ->andReturn(null);

        foreach ($iterator as $key => $entry) {
            $this->assertTrue(false);
        }
    }
}
