<?php

namespace Squirrel\Entities\Tests;

use Squirrel\Entities\Action\MultiCountEntries;
use Squirrel\Entities\Action\MultiSelectEntries;
use Squirrel\Entities\Action\MultiSelectEntriesFreeform;
use Squirrel\Entities\MultiRepositoryBuilderReadOnly;
use Squirrel\Entities\MultiRepositoryReadOnly;
use Squirrel\Entities\MultiRepositoryReadOnlyInterface;

class MultiRepositoryBuilderReadOnlyTest extends \PHPUnit\Framework\TestCase
{
    public function testSelect()
    {
        $multiRepository = \Mockery::mock(MultiRepositoryReadOnlyInterface::class);

        $multiRepositoryBuilder = new MultiRepositoryBuilderReadOnly($multiRepository);

        $this->assertEquals(new MultiSelectEntries($multiRepository), $multiRepositoryBuilder->select());
    }

    public function testSelectFreeform()
    {
        $multiRepository = \Mockery::mock(MultiRepositoryReadOnlyInterface::class);

        $multiRepositoryBuilder = new MultiRepositoryBuilderReadOnly($multiRepository);

        $this->assertEquals(
            new MultiSelectEntriesFreeform($multiRepository),
            $multiRepositoryBuilder->selectFreeform()
        );
    }

    public function testCount()
    {
        $multiRepository = \Mockery::mock(MultiRepositoryReadOnlyInterface::class);

        $multiRepositoryBuilder = new MultiRepositoryBuilderReadOnly($multiRepository);

        $this->assertEquals(new MultiCountEntries($multiRepository), $multiRepositoryBuilder->count());
    }

    public function testSelectNoConstructorArguments()
    {
        $multiRepositoryBuilder = new MultiRepositoryBuilderReadOnly();

        $this->assertEquals(new MultiSelectEntries(new MultiRepositoryReadOnly()), $multiRepositoryBuilder->select());
    }
}
