<?php

namespace Squirrel\Entities\Tests;

use Squirrel\Entities\Builder\MultiCountEntries;
use Squirrel\Entities\Builder\MultiSelectEntries;
use Squirrel\Entities\Builder\MultiSelectEntriesFreeform;
use Squirrel\Entities\MultiRepositoryBuilderReadOnly;
use Squirrel\Entities\MultiRepositoryReadOnly;
use Squirrel\Entities\MultiRepositoryReadOnlyInterface;

class MultiRepositoryBuilderReadOnlyTest extends \PHPUnit\Framework\TestCase
{
    public function testSelect(): void
    {
        $multiRepository = \Mockery::mock(MultiRepositoryReadOnlyInterface::class);

        $multiRepositoryBuilder = new MultiRepositoryBuilderReadOnly($multiRepository);

        $this->assertEquals(new MultiSelectEntries($multiRepository), $multiRepositoryBuilder->select());
    }

    public function testSelectFreeform(): void
    {
        $multiRepository = \Mockery::mock(MultiRepositoryReadOnlyInterface::class);

        $multiRepositoryBuilder = new MultiRepositoryBuilderReadOnly($multiRepository);

        $this->assertEquals(
            new MultiSelectEntriesFreeform($multiRepository),
            $multiRepositoryBuilder->selectFreeform(),
        );
    }

    public function testCount(): void
    {
        $multiRepository = \Mockery::mock(MultiRepositoryReadOnlyInterface::class);

        $multiRepositoryBuilder = new MultiRepositoryBuilderReadOnly($multiRepository);

        $this->assertEquals(new MultiCountEntries($multiRepository), $multiRepositoryBuilder->count());
    }

    public function testSelectNoConstructorArguments(): void
    {
        $multiRepositoryBuilder = new MultiRepositoryBuilderReadOnly();

        $this->assertEquals(new MultiSelectEntries(new MultiRepositoryReadOnly()), $multiRepositoryBuilder->select());
    }
}
