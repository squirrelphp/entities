<?php

namespace Squirrel\Entities\Tests;

use Squirrel\Entities\Action\MultiUpdateEntries;
use Squirrel\Entities\Action\MultiUpdateEntriesFreeform;
use Squirrel\Entities\MultiRepositoryBuilderWriteable;
use Squirrel\Entities\MultiRepositoryWriteable;
use Squirrel\Entities\MultiRepositoryWriteableInterface;

class MultiRepositoryBuilderWriteableTest extends \PHPUnit\Framework\TestCase
{
    public function testUpdate()
    {
        $multiRepository = \Mockery::mock(MultiRepositoryWriteableInterface::class);

        $multiRepositoryBuilder = new MultiRepositoryBuilderWriteable($multiRepository);

        $this->assertEquals(new MultiUpdateEntries($multiRepository), $multiRepositoryBuilder->update());
    }

    public function testUpdateFreeform()
    {
        $multiRepository = \Mockery::mock(MultiRepositoryWriteableInterface::class);

        $multiRepositoryBuilder = new MultiRepositoryBuilderWriteable($multiRepository);

        $this->assertEquals(
            new MultiUpdateEntriesFreeform($multiRepository),
            $multiRepositoryBuilder->updateFreeform()
        );
    }

    public function testUpdateNoConstructorArguments()
    {
        $multiRepositoryBuilder = new MultiRepositoryBuilderWriteable();

        $this->assertEquals(new MultiUpdateEntries(new MultiRepositoryWriteable()), $multiRepositoryBuilder->update());
    }
}
