<?php

namespace Squirrel\Entities\Tests;

use Squirrel\Entities\RepositoryConfig;
use Squirrel\Entities\RepositoryReadOnly;
use Squirrel\Entities\RepositoryWriteable;
use Squirrel\Entities\Tests\TestClasses\TicketMessageRepositoryReadOnlyDifferentRepositoryVariableWithin;
use Squirrel\Entities\Tests\TestClasses\TicketRepositoryBuilderReadOnly;
use Squirrel\Entities\Tests\TestClasses\TicketRepositoryBuilderWriteable;
use Squirrel\Entities\Tests\TestClasses\TicketRepositoryReadOnlyDifferentRepositoryBuilderVariableWithin;
use Squirrel\Entities\Transaction;
use Squirrel\Queries\Exception\DBInvalidOptionException;
use Squirrel\Queries\TestHelpers\DBInterfaceForTests;

class TransactionTest extends \PHPUnit\Framework\TestCase
{
    private $repositoryConfig;

    protected function setUp(): void
    {
        $this->repositoryConfig = new RepositoryConfig(
            '',
            'example',
            [
                'id' => 'id',
                'first_name' => 'firstName',
                'last_name' => 'lastName',
                'street' => 'street',
                'number' => 'number',
                'float_val' => 'floatVal',
                'is_great_yay' => 'isGreat',
            ],
            [
                'id' => 'id',
                'firstName' => 'first_name',
                'lastName' => 'last_name',
                'street' => 'street',
                'number' => 'number',
                'floatVal' => 'float_val',
                'isGreat' => 'is_great_yay',
            ],
            TestClasses\ObjData::class,
            [
                'id' => 'int',
                'firstName' => 'string',
                'lastName' => 'string',
                'street' => 'string',
                'number' => 'int',
                'floatVal' => 'float',
                'isGreat' => 'unknown',
            ],
            [
                'id' => false,
                'firstName' => false,
                'lastName' => false,
                'street' => false,
                'number' => false,
                'floatVal' => false,
                'isGreat' => false,
            ]
        );
    }

    public function testRun()
    {
        // Transaction function to execute
        $function = function () {
            return 5;
        };

        /**
         * Initialize DB mock
         *
         * @var DBInterfaceForTests|\Mockery\MockInterface $db
         */
        $db = \Mockery::mock(DBInterfaceForTests::class)->makePartial();

        // DB call - basically just passing through the call to DBInterface::transaction
        $db
            ->shouldReceive('transaction')
            ->once()
            ->with(\Mockery::mustBe($function))
            ->andReturnUsing($function);

        // Transaction handler instance
        $transactionHandler = new Transaction($db);

        // Give the transaction handler the callback (no arguments)
        $result = $transactionHandler->run($function);

        // Check that the function returned the correct result
        $this->assertEquals(5, $result);
    }

    public function testRunWithArguments()
    {
        // The three arguments used
        $a = 2;
        $b = 3;
        $c = 37;

        // Transaction function to execute
        $function = function ($a, $b, $c) {
            return $a + $b + $c;
        };

        /**
         * Initialize DB mock
         *
         * @var DBInterfaceForTests|\Mockery\MockInterface $db
         */
        $db = \Mockery::mock(DBInterfaceForTests::class)->makePartial();

        // DB call - basically just passing through the call to DBInterface::transaction
        $db
            ->shouldReceive('transaction')
            ->once()
            ->with(\Mockery::mustBe($function), \Mockery::mustBe($a), \Mockery::mustBe($b), \Mockery::mustBe($c))
            ->andReturnUsing(function ($function, $a, $b, $c) {
                return $function($a, $b, $c);
            });

        // Transaction handler instance
        $transactionHandler = new Transaction($db);

        // Give the transaction handler the callback plus arguments
        $result = $transactionHandler->run($function, $a, $b, $c);

        // Check that the function calculated the correct result
        $this->assertEquals(42, $result);
    }

    public function testRunFromRepositoriesWithArguments()
    {
        // The three arguments used
        $a = 2;
        $b = 3;
        $c = 37;

        // Transaction function to execute
        $function = function ($a, $b, $c) {
            return $a + $b + $c;
        };

        /**
         * Initialize DB mock
         *
         * @var DBInterfaceForTests|\Mockery\MockInterface $db
         */
        $db = \Mockery::mock(DBInterfaceForTests::class)->makePartial();

        // DB call - basically just passing through the call to DBInterface::transaction
        $db
            ->shouldReceive('transaction')
            ->once()
            ->with(\Mockery::mustBe($function), \Mockery::mustBe($a), \Mockery::mustBe($b), \Mockery::mustBe($c))
            ->andReturnUsing(function ($function, $a, $b, $c) {
                return $function($a, $b, $c);
            });

        $repositories = [
            new RepositoryReadOnly($db, $this->repositoryConfig),
            new TicketRepositoryBuilderReadOnly(new RepositoryReadOnly($db, $this->repositoryConfig)),
            new TicketRepositoryBuilderWriteable(new RepositoryWriteable($db, $this->repositoryConfig)),
        ];

        // Transaction handler instance
            $transactionHandler = Transaction::withRepositories($repositories);

        // Give the transaction handler the callback plus arguments
        $result = $transactionHandler->run($function, $a, $b, $c);

        // Check that the function calculated the correct result
        $this->assertEquals(42, $result);
    }

    public function testFromRepositoriesNoClasses()
    {
        $this->expectException(DBInvalidOptionException::class);

        $repositories = [];

        Transaction::withRepositories($repositories);
    }

    public function testFromRepositoriesNoRepository()
    {
        $this->expectException(DBInvalidOptionException::class);

        $repositories = [
            new \stdClass()
        ];

        Transaction::withRepositories($repositories);
    }

    public function testFromRepositoriesBuilderRepositoryWithDifferentReflection()
    {
        $this->expectException(DBInvalidOptionException::class);

        /**
         * Initialize DB mock
         *
         * @var DBInterfaceForTests|\Mockery\MockInterface $db
         */
        $db = \Mockery::mock(DBInterfaceForTests::class)->makePartial();

        $repositories = [
            new TicketRepositoryReadOnlyDifferentRepositoryBuilderVariableWithin(
                new RepositoryReadOnly($db, $this->repositoryConfig)
            )
        ];

        Transaction::withRepositories($repositories);
    }

    public function testFromRepositoriesBaseRepositoryWithDifferentReflection()
    {
        $this->expectException(DBInvalidOptionException::class);

        $repositories = [
            new TicketMessageRepositoryReadOnlyDifferentRepositoryVariableWithin()
        ];

        Transaction::withRepositories($repositories);
    }

    public function testFromRepositoriesDifferentConnections()
    {
        $this->expectException(DBInvalidOptionException::class);

        /**
         * Initialize DB mock
         *
         * @var DBInterfaceForTests|\Mockery\MockInterface $db
         */
        $db = \Mockery::mock(DBInterfaceForTests::class)->makePartial();

        /**
         * Initialize second DB mock
         *
         * @var DBInterfaceForTests|\Mockery\MockInterface $db
         */
        $db2 = \Mockery::mock(DBInterfaceForTests::class)->makePartial();

        $repositories = [
            new RepositoryReadOnly($db, $this->repositoryConfig),
            new TicketRepositoryBuilderReadOnly(new RepositoryReadOnly($db2, $this->repositoryConfig)),
        ];

        // Transaction handler instance
        Transaction::withRepositories($repositories);
    }
}
