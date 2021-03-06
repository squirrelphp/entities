<?php

namespace Squirrel\Entities\Tests;

use Squirrel\Entities\RepositoryConfig;
use Squirrel\Entities\RepositoryReadOnly;
use Squirrel\Entities\RepositorySelectQuery;
use Squirrel\Entities\RepositoryWriteable;
use Squirrel\Entities\Tests\TestClasses\ObjData;
use Squirrel\Queries\DBInterface;
use Squirrel\Queries\DBSelectQueryInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;
use Squirrel\Queries\LargeObject;
use Squirrel\Queries\TestHelpers\DBInterfaceForTests;

class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * DB data and objects used
     *
     * @var array
     */
    private $basicData;

    /**
     * @var DBInterface
     */
    private $db;

    /**
     * @var RepositoryWriteable
     */
    private $repository;

    /**
     * @var RepositoryConfig
     */
    private $repositoryConfig;

    /**
     * Initialize for every test in this class
     */
    protected function setUp(): void
    {
        \Hamcrest\Util::registerGlobalFunctions();

        $picture1 = md5('somevalue', true);
        $picture2 = md5('othervalue', true);

        $obj1 = new TestClasses\ObjData();
        $obj1->id = 5;
        $obj1->firstName = 'Andreas';
        $obj1->lastName = 'Baumann';
        $obj1->street = 'Müllerstrasse';
        $obj1->number = 888;
        $obj1->floatVal = 13.93;
        $obj1->isGreat = true;
        $obj1->picture = $picture1;

        $obj2 = new TestClasses\ObjData();
        $obj2->id = 13;
        $obj2->firstName = 'Ben';
        $obj2->lastName = 'Baumann';
        $obj2->street = 'Mustermann';
        $obj2->number = 934;
        $obj2->floatVal = 7.2;
        $obj2->isGreat = false;
        $obj2->picture = $picture2;

        $this->basicData = [
            'dbResults1' => [
                [
                    'id' => 5,
                    'first_name' => 'Andreas',
                    'last_name' => 'Baumann',
                    'street' => 'Müllerstrasse',
                    'number' => '888',
                    'float_val' => '13.93',
                    'is_great_yay' => '1',
                    'blabla' => '5',
                    'picture' => $picture1,
                ],
            ],
            'dbResults2' => [
                [
                    'id' => 5,
                    'first_name' => 'Andreas',
                    'last_name' => 'Baumann',
                    'street' => 'Müllerstrasse',
                    'number' => '888',
                    'float_val' => '13.93',
                    'is_great_yay' => '1',
                    'blabla' => '5',
                    'picture' => $picture1,
                ],
                [
                    'id' => 13,
                    'first_name' => 'Ben',
                    'last_name' => 'Baumann',
                    'street' => 'Mustermann',
                    'number' => '934',
                    'float_val' => '7.2',
                    'is_great_yay' => '0',
                    'blabla' => '77',
                    'picture' => $picture2,
                ],
            ],
            'obj1' => $obj1,
            'obj2' => $obj2,
        ];

        // Initialize DB mock
        $this->db = \Mockery::mock(DBInterfaceForTests::class)->makePartial();

        // Repository configuration
        $this->repositoryConfig = new RepositoryConfig(
            'defaultConnection',
            'example',
            [
                'id' => 'id',
                'first_name' => 'firstName',
                'last_name' => 'lastName',
                'street' => 'street',
                'number' => 'number',
                'float_val' => 'floatVal',
                'is_great_yay' => 'isGreat',
                'picture' => 'picture',
            ],
            [
                'id' => 'id',
                'firstName' => 'first_name',
                'lastName' => 'last_name',
                'street' => 'street',
                'number' => 'number',
                'floatVal' => 'float_val',
                'isGreat' => 'is_great_yay',
                'picture' => 'picture',
            ],
            TestClasses\ObjData::class,
            [
                'id' => 'int',
                'firstName' => 'string',
                'lastName' => 'string',
                'street' => 'string',
                'number' => 'int',
                'floatVal' => 'float',
                'isGreat' => 'bool',
                'picture' => 'blob',
            ],
            [
                'id' => false,
                'firstName' => false,
                'lastName' => false,
                'street' => true,
                'number' => false,
                'floatVal' => false,
                'isGreat' => false,
                'picture' => true,
            ],
            'id'
        );

        // Initialize repository
        $this->repository = new RepositoryWriteable($this->db, $this->repositoryConfig);
    }

    public function testConnectionNameInConfig()
    {
        $this->assertSame('defaultConnection', $this->repositoryConfig->getConnectionName());
    }

    /**
     * Set up mock DB for fetchAll
     *
     * @param array $query
     * @param array $results
     */
    protected function dbSetupFetchAll(array $query, array $results)
    {
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($query)
            ->andReturn($results);
    }

    public function testFetchAll()
    {
        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $results = $this->repository->fetchAll([
            'where' => [
                'lastName' => 'Baumann',
            ],
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals([$this->basicData['obj1'], $this->basicData['obj2']], $results);
    }

    public function testFetchAllWithLock()
    {
        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
                $this->db->quoteIdentifier('is_great_yay') . ' = ?' => 1,
            ],
            'lock' => true,
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $results = $this->repository->fetchAll([
            'where' => [
                'lastName' => 'Baumann',
                ':isGreat: = ?' => true,
            ],
            'lock' => true,
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals([$this->basicData['obj1'], $this->basicData['obj2']], $results);
    }

    public function testFetchAllNoRestrictions()
    {
        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];

        // Define the structured query we expect to generate
        $query = [
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $results = $this->repository->fetchAll([]);

        // Make sure the correct objects were returned
        $this->assertEquals([$this->basicData['obj1'], $this->basicData['obj2']], $results);
    }

    public function testFetchAllNoRestrictionsLimitOffset()
    {
        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];

        // Define the structured query we expect to generate
        $query = [
            'limit' => 2,
            'offset' => 5,
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $results = $this->repository->fetchAll(['limit' => 2, 'offset' => 5]);

        // Make sure the correct objects were returned
        $this->assertEquals([$this->basicData['obj1'], $this->basicData['obj2']], $results);
    }

    public function testFetchAllLimitOffsetOrderByNameOnlySomeFields()
    {
        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];

        // Define the structured query we expect to generate
        $query = [
            'fields' => [
                'first_name',
                'street',
            ],
            'order' => [
                'last_name' => 'DESC',
                'RAND()',
            ],
            'limit' => 2,
            'offset' => 5,
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $results = $this->repository->fetchAll([
            'fields' => [
                'firstName',
                'street',
            ],
            'order' => [
                'lastName' => 'DESC',
                'RAND()',
            ],
            'limit' => 2,
            'offset' => 5,
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals([$this->basicData['obj1'], $this->basicData['obj2']], $results);
    }

    public function testFetchAllFlattenedField()
    {
        // What values we want to see and return in our DB class
        $results = [['id' => '63'], ['id' => '87']];

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'fields' => [
                'id',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $results = $this->repository->fetchAllAndFlatten([
            'where' => [
                'lastName' => 'Baumann',
            ],
            'fields' => [
                'id',
            ],
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals([63, 87], $results);

        // Make call to repository
        $results = $this->repository->fetchAllAndFlatten([
            'where' => [
                'lastName' => 'Baumann',
            ],
            'field' => 'id',
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals([63, 87], $results);
    }

    public function testFetchAllFlattenedFields()
    {
        // What values we want to see and return in our DB class
        $results = [['id' => '63', 'first_name' => 'ladida'], ['id' => '87', 'first_name' => 'nice']];

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'fields' => [
                'id',
                'first_name',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $results = $this->repository->fetchAllAndFlatten([
            'where' => [
                'lastName' => 'Baumann',
            ],
            'fields' => [
                'id',
                'firstName',
            ],
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals([63, 'ladida', 87, 'nice'], $results);
    }

    public function testFetchAllWithNULL()
    {
        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];
        $results[1]['street'] = null;

        $obj2 = clone $this->basicData['obj2'];
        $obj2->street = null;

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'street' => null,
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $results = $this->repository->fetchAll([
            'where' => [
                'street' => null,
            ],
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals([$this->basicData['obj1'], $obj2], $results);
    }

    public function testFetchAllComplexWhereAndOrder()
    {
        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => ['Baumann', 'Rotmann', 'Salamander'],
                'first_name' => ['Laumann'],
                $this->db->quoteIdentifier('is_great_yay') . ' >= ? OR ' .
                $this->db->quoteIdentifier('is_great_yay') . ' <= ?' => [
                    13,
                    6,
                ],
                $this->db->quoteIdentifier('last_name') . ' != ' . $this->db->quoteIdentifier('first_name'),
            ],
            'order' => [
                'IF(' . $this->db->quoteIdentifier('is_great_yay') . ',0,1)' => 'ASC',
                'IF(' . $this->db->quoteIdentifier('is_great_yay') . ',0,1)',
                'last_name' => 'DESC',
                'first_name',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $results = $this->repository->fetchAll([
            'where' => [
                'lastName' => ['Baumann', 'Rotmann', 'Salamander'],
                'firstName' => ['Laumann'],
                ':isGreat: >= ? OR :isGreat: <= ?' => [13, 6],
                ':lastName: != :firstName:',
            ],
            'order' => [
                'IF(:isGreat:,0,1)' => 'ASC',
                'IF(:isGreat:,0,1)',
                'lastName' => 'DESC',
                'firstName',
            ],
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals([$this->basicData['obj1'], $this->basicData['obj2']], $results);
    }

    /**
     * Set up mock DB for select cyclus
     *
     * @param array $query
     * @param array $results
     */
    protected function dbSetupSelect(array $query, array $results)
    {
        $dbSelectQuery = \Mockery::mock(DBSelectQueryInterface::class);

        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($query)
            ->andReturn($dbSelectQuery);

        $this->db
            ->shouldReceive('fetch')
            ->once()
            ->with($dbSelectQuery)
            ->andReturn($results);

        $this->db
            ->shouldReceive('clear')
            ->once()
            ->with($dbSelectQuery);
    }

    public function testFetchOne()
    {
        // What values returned by the findBy method
        $selectResults = $this->basicData['dbResults1'];

        $expectedQuery = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'order' => [
                'first_name',
            ],
            'table' => $this->repositoryConfig->getTableName(),
            'limit' => 1,
        ];

        $this->dbSetupSelect($expectedQuery, $selectResults[0]);

        // Make call to repository
        $results = $this->repository->fetchOne([
            'where' => [
                'lastName' => 'Baumann',
            ],
            'order' => [
                'firstName',
            ],
        ]);

        // Make sure the correct results were returned
        $this->assertEquals($this->basicData['obj1'], $results);
    }

    public function testFetchOneValidLimit()
    {
        // What values returned by the findBy method
        $selectResults = $this->basicData['dbResults1'];

        $expectedQuery = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'order' => [
                'first_name',
            ],
            'table' => $this->repositoryConfig->getTableName(),
            'limit' => 1,
        ];

        $this->dbSetupSelect($expectedQuery, $selectResults[0]);

        // Make call to repository
        $results = $this->repository->fetchOne([
            'where' => [
                'lastName' => 'Baumann',
            ],
            'order' => [
                'firstName',
            ],
            'limit' => 1,
        ]);

        // Make sure the correct results were returned
        $this->assertEquals($this->basicData['obj1'], $results);
    }

    public function testCount()
    {
        // What values we want to see and return in our DB class
        $dbResult = ['num' => '13'];

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => ['Baumann', 'Rotmann', 'Salamander'],
                'first_name' => ['Laumann'],
                $this->db->quoteIdentifier('is_great_yay') . ' >= ? OR ' .
                $this->db->quoteIdentifier('is_great_yay') . ' <= ?' => [
                    13,
                    6,
                ],
                $this->db->quoteIdentifier('last_name') . ' != ' . $this->db->quoteIdentifier('first_name'),
            ],
            'fields' => [
                'num' => 'COUNT(*)',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // What we expect to get
        $this->db
            ->shouldReceive('fetchOne')
            ->once()
            ->with($query)
            ->andReturn($dbResult);

        // Make call to repository
        $result = $this->repository->count([
            'where' => [
                'lastName' => ['Baumann', 'Rotmann', 'Salamander'],
                'firstName' => ['Laumann'],
                ':isGreat: >= ? OR :isGreat: <= ?' => [13, 6],
                ':lastName: != :firstName:',
            ],
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals($dbResult['num'], $result);

        // What values we want to see and return in our DB class
        $dbResult = ['num' => '13'];

        // Define the structured query we expect to generate
        $query = [
            'fields' => [
                'num' => 'COUNT(*)',
            ],
            'lock' => true,
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // What we expect to get
        $this->db
            ->shouldReceive('fetchOne')
            ->once()
            ->with($query)
            ->andReturn($dbResult);

        // Make call to repository
        $result = $this->repository->count([
            'lock' => true,
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals($dbResult['num'], $result);
    }

    public function testUpdate()
    {
        // What values we want to see and return in our DB class
        $expectedResults = 17;

        // Define the structured query we expect to generate
        $query = [
            $this->repositoryConfig->getTableName(),
            [
                'last_name' => 'Rotmann',
                'first_name' => 'Laumann',
                $this->db->quoteIdentifier('street') . ' = CONCAT(?,?)' => ['First', 'Second'],
                $this->db->quoteIdentifier('last_name') . ' = 13',
            ],
            [
                'last_name' => ['Baumann', 'Rotmann', 'Salamander'],
                'first_name' => ['Laumann'],
                $this->db->quoteIdentifier('is_great_yay') . ' >= ? OR ' .
                $this->db->quoteIdentifier('is_great_yay') . ' <= ?' => [
                    13,
                    6,
                ],
                $this->db->quoteIdentifier('last_name') . ' != ' . $this->db->quoteIdentifier('first_name'),
            ],
        ];

        // What we expect to get
        $this->db
            ->shouldReceive('update')
            ->once()
            ->with($query[0], $query[1], $query[2])
            ->andReturn($expectedResults);

        // Make call to repository
        $results = $this->repository->update([
            'lastName' => 'Rotmann',
            'firstName' => 'Laumann',
            ':street: = CONCAT(?,?)' => ['First', 'Second'],
            ':lastName: = 13',
        ], [
            'lastName' => ['Baumann', 'Rotmann', 'Salamander'],
            'firstName' => ['Laumann'],
            ':isGreat: >= ? OR :isGreat: <= ?' => [13, 6],
            ':lastName: != :firstName:',
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals($expectedResults, $results);
    }

    public function testUpdateWithNULL()
    {
        // What values we want to see and return in our DB class
        $expectedResults = 17;

        // Define the structured query we expect to generate
        $query = [
            $this->repositoryConfig->getTableName(),
            [
                'last_name' => 'Rotmann',
                'street' => null,
            ],
            [
                'last_name' => ['Baumann', 'Rotmann', 'Salamander'],
                'first_name' => ['Laumann'],
            ],
        ];

        // What we expect to get
        $this->db
            ->shouldReceive('update')
            ->once()
            ->with($query[0], $query[1], $query[2])
            ->andReturn($expectedResults);

        // Make call to repository
        $results = $this->repository->update([
            'lastName' => 'Rotmann',
            'street' => null,
        ], [
            'lastName' => ['Baumann', 'Rotmann', 'Salamander'],
            'firstName' => ['Laumann'],
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals($expectedResults, $results);
    }

    public function testInsert()
    {
        // Convert object to array so we can use it as calling argument
        $objectAsArray = (array)$this->basicData['obj1'];
        unset($objectAsArray['unused']);

        // Convert types for exact matching
        $processedAddArray = $this->basicData['dbResults1'][0];
        unset($processedAddArray['blabla']);
        $processedAddArray['number'] = intval($processedAddArray['number']);
        $processedAddArray['float_val'] = floatval($processedAddArray['float_val']);
        $processedAddArray['is_great_yay'] = intval($processedAddArray['is_great_yay']);
        $processedAddArray['picture'] = new LargeObject($processedAddArray['picture']);

        // Last insert ID
        $lastInsertId = 77;

        // Set up DB class mock
        $this->db
            ->shouldReceive('insert')
            ->once()
            ->with(
                \Mockery::mustBe($this->repositoryConfig->getTableName()),
                equalTo($processedAddArray),
                \Mockery::mustBe('id')
            )
            ->andReturn($lastInsertId);

        // Make call to repository
        $results = $this->repository->insert($objectAsArray, true);

        // Make sure the correct objects were returned
        $this->assertEquals(77, $results);
    }

    public function testInsertNULL()
    {
        // Convert object to array so we can use it as calling argument
        $objectAsArray = (array)$this->basicData['obj1'];
        unset($objectAsArray['unused']);
        $objectAsArray['street'] = null;

        // Convert types for exact matching
        $processedAddArray = $this->basicData['dbResults1'][0];
        unset($processedAddArray['blabla']);
        $processedAddArray['number'] = intval($processedAddArray['number']);
        $processedAddArray['float_val'] = floatval($processedAddArray['float_val']);
        $processedAddArray['is_great_yay'] = intval($processedAddArray['is_great_yay']);
        $processedAddArray['street'] = null;
        $processedAddArray['picture'] = new LargeObject($processedAddArray['picture']);

        // Last insert ID
        $lastInsertId = 77;

        // Set up DB class mock
        $this->db
            ->shouldReceive('insert')
            ->once()
            ->with(
                \Mockery::mustBe($this->repositoryConfig->getTableName()),
                equalTo($processedAddArray),
                \Mockery::mustBe('id')
            )
            ->andReturn($lastInsertId);

        // Make call to repository
        $results = $this->repository->insert($objectAsArray, true);

        // Make sure the correct objects were returned
        $this->assertEquals(77, $results);
    }

    public function testInsertWithoutInsertId()
    {
        // Convert object to array so we can use it as calling argument
        $objectAsArray = (array)$this->basicData['obj1'];
        unset($objectAsArray['unused']);

        // Convert types for exact matching
        $processedAddArray = $this->basicData['dbResults1'][0];
        unset($processedAddArray['blabla']);
        $processedAddArray['number'] = intval($processedAddArray['number']);
        $processedAddArray['float_val'] = floatval($processedAddArray['float_val']);
        $processedAddArray['is_great_yay'] = intval($processedAddArray['is_great_yay']);
        $processedAddArray['picture'] = new LargeObject($processedAddArray['picture']);

        // Set up DB class mock
        $this->db
            ->shouldReceive('insert')
            ->once()
            ->with(\Mockery::mustBe($this->repositoryConfig->getTableName()), equalTo($processedAddArray));

        // Make call to repository
        $results = $this->repository->insert($objectAsArray, false);

        // Make sure the correct objects were returned
        $this->assertEquals(null, $results);
    }

    public function testInsertOrUpdate()
    {
        // Convert object to array so we can use it as calling argument
        $objectAsArray = [
            'id' => 5,
            'firstName' => 'Andreas',
            'lastName' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => '888',
            'floatVal' => '13.93',
            'isGreat' => '1',
        ];

        // Convert object to array so we can use it as calling argument
        $insertAsArray = [
            'id' => 5,
            'first_name' => 'Andreas',
            'last_name' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => 888,
            'float_val' => 13.93,
            'is_great_yay' => 1,
        ];

        // Set up DB class mock
        $this->dbSetupInsertOrUpdate('example', $insertAsArray, ['id'], null);

        // Make call to repository
        $this->repository->insertOrUpdate($objectAsArray, ['id']);

        // Make sure the correct objects were returned
        $this->assertTrue(true);
    }

    /**
     * Set up mock DB for insert ON DUPLICATE KEY UPDATE tests
     *
     * @param string $tableName
     * @param array $fields
     * @param array $indexFields
     * @param array $updateFields
     */
    protected function dbSetupInsertOrUpdate(
        string $tableName,
        array $fields,
        array $indexFields,
        ?array $updateFields
    ) {
        // DB calls
        $this->db
            ->shouldReceive('insertOrUpdate')
            ->once()
            ->with(
                \Mockery::mustBe($tableName),
                \Mockery::mustBe($fields),
                \Mockery::mustBe($indexFields),
                \Mockery::mustBe($updateFields)
            );
    }

    public function testInsertOrUpdateNoChange()
    {
        // Convert object to array so we can use it as calling argument
        $objectAsArray = [
            'id' => 5,
            'firstName' => 'Andreas',
            'lastName' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => '888',
            'floatVal' => '13.93',
            'isGreat' => '1',
        ];

        // Convert object to array so we can use it as calling argument
        $insertAsArray = [
            'id' => 5,
            'first_name' => 'Andreas',
            'last_name' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => 888,
            'float_val' => 13.93,
            'is_great_yay' => 1,
        ];

        // Set up DB class mock
        $this->dbSetupInsertOrUpdate('example', $insertAsArray, ['id'], null);

        // Make call to repository
        $this->repository->insertOrUpdate($objectAsArray, ['id']);

        // Make sure the correct objects were returned
        $this->assertTrue(true);
    }

    public function testInsertOrUpdateCustomUpdate()
    {
        // Convert object to array so we can use it as calling argument
        $objectAsArray = [
            'id' => 5,
            'firstName' => 'Andreas',
            'lastName' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => '888',
            'floatVal' => '13.93',
            'isGreat' => '1',
        ];

        // Convert object to array so we can use it as calling argument
        $insertAsArray = [
            'id' => 5,
            'first_name' => 'Andreas',
            'last_name' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => 888,
            'float_val' => 13.93,
            'is_great_yay' => 1,
        ];

        // Convert object to array so we can use it as calling argument
        $updateAsArray = [
            'number' => 888,
            'float_val' => 13.93,
            'is_great_yay' => 1,
        ];

        // Set up DB class mock
        $this->dbSetupInsertOrUpdate('example', $insertAsArray, ['id'], $updateAsArray);

        // Make call to repository
        $this->repository->insertOrUpdate($objectAsArray, ['id'], [
            'number' => '888',
            'floatVal' => '13.93',
            'isGreat' => '1',
        ]);

        // Make sure the correct objects were returned
        $this->assertTrue(true);
    }

    public function testInsertOrUpdateCustomUpdate2()
    {
        // Convert object to array so we can use it as calling argument
        $objectAsArray = [
            'id' => 5,
            'firstName' => 'Andreas',
            'lastName' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => '888',
            'floatVal' => '13.93',
            'isGreat' => '1',
        ];

        // Convert object to array so we can use it as calling argument
        $insertAsArray = [
            'id' => 5,
            'first_name' => 'Andreas',
            'last_name' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => 888,
            'float_val' => 13.93,
            'is_great_yay' => 1,
        ];

        // Convert object to array so we can use it as calling argument
        $updateAsArray = [
            $this->db->quoteIdentifier('number') . ' = ' . $this->db->quoteIdentifier('number') . ' + 1',
            'float_val' => 13.93,
            'is_great_yay' => 1,
        ];

        // Set up DB class mock
        $this->dbSetupInsertOrUpdate('example', $insertAsArray, ['id'], $updateAsArray);

        // Make call to repository
        $this->repository->insertOrUpdate($objectAsArray, ['id'], [
            ':number: = :number: + 1',
            'floatVal' => '13.93',
            'isGreat' => '1',
        ]);

        // Make sure the correct objects were returned
        $this->assertTrue(true);
    }

    public function testDelete()
    {
        // What values we want to see and return in our DB class
        $expectedResults = 17;

        // Define the structured query we expect to generate
        $query = [
            'last_name' => ['Baumann', 'Rotmann', 'Salamander'],
            'first_name' => ['Laumann'],
            $this->db->quoteIdentifier('is_great_yay') . ' >= ? OR ' .
            $this->db->quoteIdentifier('is_great_yay') . ' <= ?' => [
                13,
                6,
            ],
            $this->db->quoteIdentifier('last_name') . ' != ' . $this->db->quoteIdentifier('first_name'),
        ];

        // What we expect to get
        $this->db
            ->shouldReceive('delete')
            ->once()
            ->with($this->repositoryConfig->getTableName(), $query)
            ->andReturn($expectedResults);

        // Make call to repository
        $results = $this->repository->delete([
            'lastName' => ['Baumann', 'Rotmann', 'Salamander'],
            'firstName' => ['Laumann'],
            ':isGreat: >= ? OR :isGreat: <= ?' => [13, 6],
            ':lastName: != :firstName:',
        ]);

        // Make sure the correct objects were returned
        $this->assertEquals($expectedResults, $results);
    }

    public function testDeleteNoWhere()
    {
        // What values we want to see and return in our DB class
        $expectedResults = 17;

        // Define the structured query we expect to generate
        $query = [];

        // What we expect to get
        $this->db
            ->shouldReceive('delete')
            ->once()
            ->with($this->repositoryConfig->getTableName(), $query)
            ->andReturn($expectedResults);

        // Make call to repository
        $results = $this->repository->delete([]);

        // Make sure the correct objects were returned
        $this->assertEquals($expectedResults, $results);
    }

    public function testSelectUnknownOption()
    {
        // Expect an exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'order' => ['id' => 'DESC'],
            'limit' => 2,
            'offset' => 5,
            'bad' => 3,
        ]);
    }

    public function testSelectNotAnArrayOption()
    {
        // Expect an exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'order' => 'dada',
        ]);
    }

    public function testSelectUnknownWhereVariable()
    {
        // Expect an exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                'unknown' => 'dada',
            ],
        ]);
    }

    public function testSelectInvalidWhereExpression()
    {
        // Expect an exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                0,
            ],
        ]);
    }

    public function testSelectUnresolvedVariableInWhereExpression()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                ':unresolvedOption: = ?' => 'test',
            ],
        ]);
    }

    public function testSelectUnknownFieldsName()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                'lastName' => 'Baumann',
            ],
            'fields' => [
                'badFieldName',
            ],
        ]);
    }

    public function testSelectIllegalFieldsName()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                'lastName' => 'Baumann',
            ],
            'fields' => [
                5,
            ],
        ]);
    }

    public function testSelectInvalidOrderExpression()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                'lastName' => 'Baumann',
            ],
            'order' => [
                0,
            ],
        ]);
    }

    public function testSelectUnresolvedOrderExpression()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                'lastName' => 'Baumann',
            ],
            'order' => [
                'IF(:unresolved:,0,1)' => 'ASC',
            ],
        ]);
    }

    public function testSelectInvalidValueArrayInArray()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                'firstName' => [['array']],
            ],
        ]);
    }

    public function testSelectWithNULLNotNullable()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                'firstName' => null,
            ],
        ]);
    }

    public function testSelectNonBooleanLock()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->count([
            'where' => [
                'firstName' => 'dada',
            ],
            'lock' => 2,
        ]);
    }

    public function testSelectMissingObjectType()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Repository configuration
        $repositoryConfig = new RepositoryConfig(
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
                // 'street' value is missing here causing an exception
                'number' => 'int',
                'floatVal' => 'float',
                'isGreat' => 'bool',
            ],
            [
                'id' => false,
                'firstName' => false,
                'lastName' => false,
                'street' => true,
                'number' => false,
                'floatVal' => false,
                'isGreat' => false,
            ]
        );

        // Initialize repository
        $repository = new RepositoryWriteable($this->db, $repositoryConfig);

        // Make call to repository
        $repository->select([
            'where' => [
                'street' => 'Baumann',
            ],
        ]);
    }

    public function testSelectInvalidObjectType()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Repository configuration
        $repositoryConfig = new RepositoryConfig(
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
                'street' => 'fantasy', // invalid type
                'number' => 'int',
                'floatVal' => 'float',
                'isGreat' => 'bool',
            ],
            [
                'id' => false,
                'firstName' => false,
                'lastName' => false,
                'street' => true,
                'number' => false,
                'floatVal' => false,
                'isGreat' => false,
            ]
        );

        // Initialize repository
        $repository = new RepositoryWriteable($this->db, $repositoryConfig);

        // Make call to repository
        $repository->select([
            'where' => [
                'street' => 'Baumann',
            ],
        ]);
    }

    public function testSelectOneWithInvalidLimit()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Options, second argument to findOneBy
        $options = [
            'where' => [
                'lastName' => 'Baumann',
            ],
            'order' => [
                'firstName',
            ],
            'limit' => 5,
        ];

        // Make call to repository
        $this->repository->fetchOne($options);
    }

    public function testUpdateNoChanges()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->update([], [
            'firstName' => 'Sexyhexy',
        ]);
    }

    public function testUpdateNoChangeExpression()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->update([
            0,
        ], [
            'firstName' => 'Sexyhexy',
        ]);
    }

    public function testUpdateInvalidChangeVariable()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->update([
            'firstNameInvalid' => 'Sexyhexy',
        ], [
            'lastName' => 'Baumann',
            'isGreat' => false,
        ]);
    }

    public function testUpdateInvalidChangeExpression()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->update([
            ':firstNameInvalid: = 5',
        ], [
            'lastName' => 'Baumann',
            'isGreat' => false,
        ]);
    }

    public function testUpdateWithNULLNotNullable()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Make call to repository
        $this->repository->update([
            'lastName' => null,
        ], [
            'lastName' => ['Baumann', 'Rotmann', 'Salamander'],
            'firstName' => ['Laumann'],
        ]);
    }

    public function testInsertUnknownFieldName()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Insert values
        $objectAsArray = [
            'invalid' => 5,
        ];

        // Make call to repository
        $this->repository->insert($objectAsArray, false);
    }

    public function testInsertNullNotNullable()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Insert values
        $objectAsArray = [
            'lastName' => null,
        ];

        // Make call to repository
        $this->repository->insert($objectAsArray, false);
    }

    public function testInsertOrUpdateUnknownFieldName()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Insert values
        $objectAsArray = [
            'id' => 5,
            'invalid' => 5,
        ];

        // Make call to repository
        $this->repository->insertOrUpdate($objectAsArray, ['id']);
    }

    public function testInsertOrUpdateIndexNotOccuringInDataArray()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Insert values
        $objectAsArray = [
            'firstName' => 'dada',
        ];

        // Make call to repository
        $this->repository->insertOrUpdate($objectAsArray, ['id']);
    }

    public function testInsertOrUpdateNullForNotNullable()
    {
        // Expect an InvalidArgument exception
        $this->expectException(DBInvalidOptionException::class);

        // Insert values
        $objectAsArray = [
            'id' => 5,
            'firstName' => null,
        ];

        // Make call to repository
        $this->repository->insertOrUpdate($objectAsArray, ['id']);
    }

    public function testBadObjValueCasting()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Bad repository config with isGreat set to unknown
        $repositoryConfig = new RepositoryConfig(
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
            ObjData::class,
            [
                'id' => 'int',
                'firstName' => 'string',
                'lastName' => 'string',
                'street' => 'string',
                'number' => 'int',
                'floatVal' => 'float',
                'isGreat' => 'unknown', // not a valid type
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

        // Initialize repository
        $repository = new RepositoryReadOnly($this->db, $repositoryConfig);

        // What values we want to see and return in our DB class
        $results = $this->basicData['dbResults2'];

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'table' => $repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->dbSetupFetchAll($query, $results);

        // Make call to repository
        $repository->fetchAll([
            'where' => [
                'lastName' => 'Baumann',
            ],
        ]);
    }

    public function testRepositoryConfigEqualFetchExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($query)
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $this->repository->select([
            'where' => [
                'lastName' => 'Baumann',
            ],
        ]);
    }

    public function testSelectExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        $dbSelectQuery = \Mockery::mock(DBSelectQueryInterface::class);

        $repositoryConfig = new RepositoryConfig(
            'defaultConnection',
            'anotherExample', // this is different!
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
                'isGreat' => 'bool',
            ],
            [
                'id' => false,
                'firstName' => false,
                'lastName' => false,
                'street' => true,
                'number' => false,
                'floatVal' => false,
                'isGreat' => false,
            ]
        );

        $wrongConfigQuery = new RepositorySelectQuery($dbSelectQuery, $repositoryConfig);

        $this->repository->fetch($wrongConfigQuery);
    }

    public function testFetchExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        $dbSelectQuery = \Mockery::mock(DBSelectQueryInterface::class);

        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($query)
            ->andReturn($dbSelectQuery);

        $this->db
            ->shouldReceive('fetch')
            ->once()
            ->with($dbSelectQuery)
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $selectResult = $this->repository->select([
            'where' => [
                'lastName' => 'Baumann',
            ],
        ]);

        $this->repository->fetch($selectResult);
    }

    public function testClearExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        $dbSelectQuery = \Mockery::mock(DBSelectQueryInterface::class);

        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($query)
            ->andReturn($dbSelectQuery);

        $this->db
            ->shouldReceive('clear')
            ->once()
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $queryResult = $this->repository->select([
            'where' => [
                'lastName' => 'Baumann',
            ],
        ]);

        $this->repository->clear($queryResult);
    }

    public function testFetchAllExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($query)
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $this->repository->fetchAll([
            'where' => [
                'lastName' => 'Baumann',
            ],
        ]);
    }

    public function testFetchAllAndFlattenExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => 'Baumann',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // Set up DB class mock
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($query)
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $this->repository->fetchAllAndFlatten([
            'where' => [
                'lastName' => 'Baumann',
            ],
        ]);
    }

    public function testCountExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Define the structured query we expect to generate
        $query = [
            'where' => [
                'last_name' => ['Baumann', 'Rotmann', 'Salamander'],
            ],
            'fields' => [
                'num' => 'COUNT(*)',
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // What we expect to get
        $this->db
            ->shouldReceive('fetchOne')
            ->once()
            ->with($query)
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $this->repository->count([
            'where' => [
                'lastName' => ['Baumann', 'Rotmann', 'Salamander'],
            ],
        ]);
    }

    public function testUpdateExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Define the structured query we expect to generate
        $query = [
            'changes' => [
                'last_name' => 'Rotmann',
            ],
            'where' => [
                'last_name' => ['Baumann', 'Rotmann', 'Salamander'],
            ],
            'table' => $this->repositoryConfig->getTableName(),
        ];

        // What we expect to get
        $this->db
            ->shouldReceive('update')
            ->once()
            ->with($query['table'], $query['changes'], $query['where'])
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $this->repository->update([
            'lastName' => 'Rotmann',
        ], [
            'lastName' => ['Baumann', 'Rotmann', 'Salamander'],
        ]);
    }

    public function testInsertExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Convert object to array so we can use it as calling argument
        $objectAsArray = (array)$this->basicData['obj1'];
        unset($objectAsArray['unused']);

        // Convert types for exact matching
        $processedAddArray = $this->basicData['dbResults1'][0];
        unset($processedAddArray['blabla']);
        $processedAddArray['number'] = intval($processedAddArray['number']);
        $processedAddArray['float_val'] = floatval($processedAddArray['float_val']);
        $processedAddArray['is_great_yay'] = intval($processedAddArray['is_great_yay']);
        $processedAddArray['picture'] = new LargeObject($processedAddArray['picture']);

        // Set up DB class mock
        $this->db
            ->shouldReceive('insert')
            ->once()
            ->with(
                \Mockery::mustBe($this->repositoryConfig->getTableName()),
                equalTo($processedAddArray),
                \Mockery::mustBe('id')
            )
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $this->repository->insert($objectAsArray, true);
    }

    public function testInsertOrUpdateExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Convert object to array so we can use it as calling argument
        $objectAsArray = [
            'id' => 5,
            'firstName' => 'Andreas',
            'lastName' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => '888',
            'floatVal' => '13.93',
            'isGreat' => '1',
        ];

        // Convert object to array so we can use it as calling argument
        $insertAsArray = [
            'id' => 5,
            'first_name' => 'Andreas',
            'last_name' => 'Baumann',
            'street' => 'Müllerstrasse',
            'number' => 888,
            'float_val' => 13.93,
            'is_great_yay' => 1,
        ];

        // Set up DB class mock
        $this->db
            ->shouldReceive('insertOrUpdate')
            ->once()
            ->with(
                \Mockery::mustBe('example'),
                \Mockery::mustBe($insertAsArray),
                \Mockery::mustBe(['id']),
                \Mockery::mustBe(null)
            )
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $this->repository->insertOrUpdate($objectAsArray, ['id']);
    }

    public function testDeleteExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Define the structured query we expect to generate
        $query = [
            'last_name' => ['Baumann', 'Rotmann', 'Salamander'],
        ];

        // What we expect to get
        $this->db
            ->shouldReceive('delete')
            ->once()
            ->with($this->repositoryConfig->getTableName(), $query)
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Make call to repository
        $this->repository->delete([
            'lastName' => ['Baumann', 'Rotmann', 'Salamander'],
        ]);
    }

    public function testNoAutoincrementAttemptInsertId()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // Repository configuration
        $this->repositoryConfig = new RepositoryConfig(
            'defaultConnection',
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
                'isGreat' => 'bool',
            ],
            [
                'id' => false,
                'firstName' => false,
                'lastName' => false,
                'street' => true,
                'number' => false,
                'floatVal' => false,
                'isGreat' => false,
            ],
            ''
        );

        // Initialize repository
        $this->repository = new RepositoryWriteable($this->db, $this->repositoryConfig);

        $this->testInsert();
    }
}
