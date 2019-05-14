<?php

namespace Squirrel\Entities\Tests\RepositoryActions;

use Squirrel\Entities\Action\MultiUpdateEntriesFreeform;
use Squirrel\Entities\MultiRepositoryWriteableInterface;
use Squirrel\Entities\RepositoryWriteableInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

class MultiUpdateEntriesFreeformTest extends \PHPUnit\Framework\TestCase
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
        $builder = new MultiUpdateEntriesFreeform($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('update')
            ->once()
            ->with([], '', [])
            ->andReturn(8);

        $results = $builder
            ->confirmFreeformQueriesAreNotRecommended('OK')
            ->writeAndReturnAffectedNumber();

        $this->assertEquals(8, $results);
    }

    public function testGetEntries()
    {
        $builder = new MultiUpdateEntriesFreeform($this->multiRepository);

        $builder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->query('UPDATE :ticket: SET :ticket.id: = ? WHERE :ticket.id: = ?')
            ->withParameters([
                13,
                5,
            ])
            ->confirmFreeformQueriesAreNotRecommended('OK');

        $this->multiRepository
            ->shouldReceive('update')
            ->once()
            ->with([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ], 'UPDATE :ticket: SET :ticket.id: = ? WHERE :ticket.id: = ?', [
                13,
                5,
            ])
            ->andReturn(565);

        $results = $builder->writeAndReturnAffectedNumber();

        $this->assertEquals(565, $results);
    }

    public function testGetEntriesWriteOnly()
    {
        $builder = new MultiUpdateEntriesFreeform($this->multiRepository);

        $builder
            ->inRepositories([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ])
            ->query('UPDATE :ticket: SET :ticket.id: = ? WHERE :ticket.id: = ?')
            ->withParameters([
                13,
                5,
            ])
            ->confirmFreeformQueriesAreNotRecommended('OK');

        $this->multiRepository
            ->shouldReceive('update')
            ->once()
            ->with([
                'ticket' => $this->repository1,
                'email' => $this->repository2,
            ], 'UPDATE :ticket: SET :ticket.id: = ? WHERE :ticket.id: = ?', [
                13,
                5,
            ])
            ->andReturn(565);

        $builder->write();

        $this->assertTrue(true);
    }

    public function testMissingConfirmation()
    {
        $this->expectException(DBInvalidOptionException::class);

        $builder = new MultiUpdateEntriesFreeform($this->multiRepository);

        $this->multiRepository
            ->shouldReceive('update')
            ->once()
            ->with([], '', [])
            ->andReturn(8);

        $builder->writeAndReturnAffectedNumber();
    }
}
