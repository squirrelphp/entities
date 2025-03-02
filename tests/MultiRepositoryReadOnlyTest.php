<?php

namespace Squirrel\Entities\Tests;

use Hamcrest\Core\IsEqual;
use Mockery\MockInterface;
use Squirrel\Entities\MultiRepositoryReadOnly;
use Squirrel\Entities\RepositoryConfig;
use Squirrel\Entities\RepositoryConfigInterface;
use Squirrel\Entities\RepositoryReadOnly;
use Squirrel\Entities\Tests\TestClasses\TicketRepositoryReadOnlyCorrectNameButInvalidDatabaseConnection;
use Squirrel\Queries\DBInterface;
use Squirrel\Queries\DBSelectQueryInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * We especially test all the arguments for validity in these test cases, in addition
 * to the main regular parts of QueryHandler for SELECT queries
 */
class MultiRepositoryReadOnlyTest extends \PHPUnit\Framework\TestCase
{
    use ErrorHandlerTrait;

    private MultiRepositoryReadOnly $queryHandler;
    /** @var DBInterface&MockInterface */
    private DBInterface $db;
    private RepositoryConfig $ticketRepositoryConfig;
    private \Squirrel\Entities\Tests\TestClasses\TicketRepositoryBuilderReadOnly $ticketRepository;
    private RepositoryConfigInterface $ticketMessageRepositoryConfig;
    private RepositoryReadOnly $ticketMessageRepository;
    private RepositoryReadOnly $emailRepository;
    private array $complicatedQuery = [];
    private array $queryFreeform = [];

    /**
     * Initialize for every test in this class
     */
    protected function setUp(): void
    {
        // Mock Database class
        $this->db = \Mockery::mock(DBInterface::class)->makePartial();
        $this->db->shouldReceive('quoteIdentifier')->andReturnUsing(function (string $identifier): string {
            if (\str_contains($identifier, ".")) {
                $parts = \array_map(
                    function ($p) {
                        return '"' . \str_replace('"', '""', $p) . '"';
                    },
                    \explode(".", $identifier),
                );

                return \implode(".", $parts);
            }

            return '"' . \str_replace('"', '""', $identifier) . '"';
        });

        // Initialize query handler so it can be used
        $this->queryHandler = new MultiRepositoryReadOnly();

        $this->ticketRepositoryConfig = new RepositoryConfig('', 'databasename.tickets', [
            'ticket_id' => 'ticketId',
            'ticket_title' => 'title',
            'ticket_floaty' => 'floaty',
            'ticket_open' => 'open',
            'ticket_status' => 'status',
            'msgNumber' => 'messagesNumber',
            'last_update' => 'lastUpdate',
            'create_date' => 'createDate',
        ], [
            'ticketId' => 'ticket_id',
            'title' => 'ticket_title',
            'floaty' => 'ticket_floaty',
            'open' => 'ticket_open',
            'status' => 'ticket_status',
            'messagesNumber' => 'msgNumber',
            'lastUpdate' => 'last_update',
            'createDate' => 'create_date',
        ], 'ObjectClass', [
            'ticketId' => 'int',
            'title' => 'string',
            'floaty' => 'float',
            'open' => 'bool',
            'status' => 'int',
            'messagesNumber' => 'int',
            'lastUpdate' => 'int',
            'createDate' => 'int',
        ], [
            'ticketId' => false,
            'title' => false,
            'floaty' => false,
            'open' => false,
            'status' => false,
            'messagesNumber' => false,
            'lastUpdate' => true,
            'createDate' => false,
        ]);

        $this->ticketRepository = new TestClasses\TicketRepositoryBuilderReadOnly(
            new RepositoryReadOnly($this->db, $this->ticketRepositoryConfig),
        );

        $this->ticketMessageRepositoryConfig = new RepositoryConfig('', 'tickets_messages', [
            'msg_id' => 'messageId',
            'ticket_id' => 'ticketId',
            'email_id' => 'emailId',
            'sender_type' => 'senderType',
            'create_date' => 'createDate',
        ], [
            'messageId' => 'msg_id',
            'ticketId' => 'ticket_id',
            'emailId' => 'email_id',
            'senderType' => 'sender_type',
            'createDate' => 'create_date',
        ], 'ObjectClass', [
            'messageId' => 'int',
            'ticketId' => 'int',
            'emailId' => 'int',
            'senderType' => 'string',
            'createDate' => 'int',
        ], [
            'messageId' => false,
            'ticketId' => false,
            'emailId' => false,
            'senderType' => false,
            'createDate' => false,
        ]);

        $this->ticketMessageRepository = new RepositoryReadOnly($this->db, $this->ticketMessageRepositoryConfig);

        $this->emailRepository = new RepositoryReadOnly($this->db, new RepositoryConfig(
            '',
            'db74.emails',
            [
                'email_id' => 'emailId',
                'to_address' => 'to',
                'from_address' => 'from',
                'automatic' => 'automatic',
                'create_date' => 'createDate',
            ],
            [
                'emailId' => 'email_id',
                'to' => 'to_address',
                'from' => 'from_address',
                'automatic' => 'automatic',
                'createDate' => 'create_date',
            ],
            'ObjectClass',
            [
                'emailId' => 'int',
                'to' => 'string',
                'from' => 'string',
                'automatic' => 'bool',
                'createDate' => 'int',
            ],
            [
                'emailId' => false,
                'to' => false,
                'from' => false,
                'automatic' => false,
                'createDate' => false,
            ],
        ));

        // Complicated query as a template to test
        $this->complicatedQuery = [
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'emailTo' => 'email.to',
                'ticket.open',
                'ticketTitle' => 'ticket.title',
                'ticketTitleLength' => 'LENGTH(:ticket.title:)',
                'updateMinusCreated' => ':ticket.lastUpdate:-:ticket.createDate:',
                'floaty' => ':ticket.floaty:',
                'booleany' => ':ticket.open:',
                'integery' => ':ticket.lastUpdate:/2',
                'updateCreatedConcat' => 'CONCAT(:ticket.lastUpdate:,:ticket.createDate:)',
            ],
            'tables' => [
                'ticket',
                ':message: LEFT JOIN :email: ' .
                'ON (:message.emailId: = :email.emailId: AND :email.automatic: = ?)' => true,
                ':message: LEFT JOIN :email: ON (:message.emailId: = :email.emailId:)',
            ],
            'where' => [
                ':ticket.ticketId: = :message.ticketId:',
                'email.to' => 'info@dada.com',
                ':ticket.open: = ?' => true,
                ':ticket.floaty: BETWEEN ? AND ?' => [5.5, 9.5],
            ],
            'group' => [
                'ticket.ticketId',
                'email.to',
                'DATE(:ticket.lastUpdate:)',
            ],
            'order' => [
                'ticket.ticketId' => 'DESC',
                'updateMinusCreated',
                '(:ticket.lastUpdate:-:ticket.createDate:)' => 'DESC',
                ':ticket.lastUpdate:+:ticket.createDate:' => 'ASC',
            ],
            'limit' => 30,
            'offset' => 7,
        ];

        // Freeform query parts
        $this->queryFreeform = [
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
            ],
            'query' => ':ticket:,:message: LEFT JOIN :email: ' .
                'ON (:message.emailId: = :email.emailId: AND :email.automatic: = ?) ' .
                'WHERE (:ticket.ticketId: = :message.ticketId:) AND (:ticket.open: = ?) ' .
                'AND (:ticket.floaty: = ?) ' .
                'GROUP BY :ticket.ticketId: ' .
                'ORDER BY :ticket.ticketId: DESC,' .
                'updateMinusCreated ASC,' .
                '(:ticket.lastUpdate:-:ticket.createDate:) DESC ' .
                'LIMIT 30',
            'parameters' => [
                true,
                true,
                9.5,
            ],
        ];
    }

    public function testMinimal(): void
    {
        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.floaty' => 'ticket.ticket_floaty',
                'ticket.open' => 'ticket.ticket_open',
                'ticket.title' => 'ticket.ticket_title',
                'ticket.lastUpdate' => 'ticket.last_update',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => [77, 88, 193],
                'ticket.ticket_open' => 1,
            ],
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'ticket.ticketId' => '54',
                'ticket.floaty' => '0.3',
                'ticket.open' => '1',
                'ticket.title' => 'Dadaism',
                'ticket.lastUpdate' => null,
            ],
        ];

        // After the data was processed according to types
        $resultsProcessed = [
            [
                'ticket.ticketId' => 54,
                'ticket.floaty' => 0.3,
                'ticket.open' => true,
                'ticket.title' => 'Dadaism',
                'ticket.lastUpdate' => null,
            ],
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        // Attempt select
        $results = $this->queryHandler->fetchAll([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'ticket.open',
                'ticket.title',
                'ticket.lastUpdate',
            ],
            'where' => [
                'ticket.ticketId' => [77, 88, 193],
                'ticket.open' => true,
            ],
        ]);

        // Make sure we received the correct sanitized results
        $this->assertSame($resultsProcessed, $results);
    }

    public function testFetchOne(): void
    {
        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.floaty' => 'ticket.ticket_floaty',
                'ticket.open' => 'ticket.ticket_open',
                'ticket.title' => 'ticket.ticket_title',
                'ticket.lastUpdate' => 'ticket.last_update',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => [77, 88, 193],
                'ticket.ticket_open' => 1,
            ],
        ];

        // What the database returns
        $resultsFromDb = [
                'ticket.ticketId' => '54',
                'ticket.floaty' => '0.3',
                'ticket.open' => '1',
                'ticket.title' => 'Dadaism',
                'ticket.lastUpdate' => null,
        ];

        // After the data was processed according to types
        $resultsProcessed = [
            'ticket.ticketId' => 54,
            'ticket.floaty' => 0.3,
            'ticket.open' => true,
            'ticket.title' => 'Dadaism',
            'ticket.lastUpdate' => null,
        ];

        $dbSelectQuery = \Mockery::mock(DBSelectQueryInterface::class);

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($dbSelectQuery);

        $this->db
            ->shouldReceive('fetch')
            ->once()
            ->with($dbSelectQuery)
            ->andReturn($resultsFromDb);

        $this->db
            ->shouldReceive('clear')
            ->once()
            ->with($dbSelectQuery);

        // Attempt select
        $results = $this->queryHandler->fetchOne([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'ticket.open',
                'ticket.title',
                'ticket.lastUpdate',
            ],
            'where' => [
                'ticket.ticketId' => [77, 88, 193],
                'ticket.open' => true,
            ],
        ]);

        // Make sure we received the correct sanitized results
        $this->assertSame($resultsProcessed, $results);
    }

    public function testFetchOneNoResult(): void
    {
        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.floaty' => 'ticket.ticket_floaty',
                'ticket.open' => 'ticket.ticket_open',
                'ticket.title' => 'ticket.ticket_title',
                'ticket.lastUpdate' => 'ticket.last_update',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => [77, 88, 193],
                'ticket.ticket_open' => 1,
            ],
        ];

        // What the database returns
        $resultsFromDb = null;

        // After the data was processed according to types
        $resultsProcessed = null;

        $dbSelectQuery = \Mockery::mock(DBSelectQueryInterface::class);

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($dbSelectQuery);

        $this->db
            ->shouldReceive('fetch')
            ->once()
            ->with($dbSelectQuery)
            ->andReturn($resultsFromDb);

        $this->db
            ->shouldReceive('clear')
            ->once()
            ->with($dbSelectQuery);

        // Attempt select
        $results = $this->queryHandler->fetchOne([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'ticket.open',
                'ticket.title',
                'ticket.lastUpdate',
            ],
            'where' => [
                'ticket.ticketId' => [77, 88, 193],
                'ticket.open' => true,
            ],
        ]);

        // Make sure we received the correct sanitized results
        $this->assertSame($resultsProcessed, $results);
    }

    public function testCount(): void
    {
        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'COUNT(*) AS "num"',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => [77, 88, 193],
                'ticket.ticket_open' => 1,
            ],
            'lock' => true,
        ];

        // What the database returns
        $resultsFromDb = [
            'num' => '33',
        ];

        // After the data was processed according to types
        $resultsProcessed = 33;

        $dbSelectQuery = \Mockery::mock(DBSelectQueryInterface::class);

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($dbSelectQuery);

        $this->db
            ->shouldReceive('fetch')
            ->once()
            ->with($dbSelectQuery)
            ->andReturn($resultsFromDb);

        $this->db
            ->shouldReceive('clear')
            ->once()
            ->with($dbSelectQuery);

        // Attempt select
        $results = $this->queryHandler->count([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'tables' => [
                'ticket',
                'message',
                'email',
            ],
            'where' => [
                'ticket.ticketId' => [77, 88, 193],
                'ticket.open' => true,
            ],
            'lock' => true,
        ]);

        // Make sure we received the correct sanitized results
        $this->assertSame($resultsProcessed, $results);
    }

    public function testFlattenedField(): void
    {
        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => 77,
                'ticket.ticket_open' => 0,
            ],
            'limit' => 3,
            'lock' => true,
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'ticket.ticketId' => '54',
            ],
            [
                'ticket.ticketId' => '33',
            ],
            [
                'ticket.ticketId' => '89',
            ],
        ];

        // After the data was processed according to types
        $resultsProcessed = [
            54,
            33,
            89,
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        // Attempt select
        $results = $this->queryHandler->fetchAllAndFlatten([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
            ],
            'where' => [
                'ticket.ticketId' => '77',
                'ticket.open' => false,
            ],
            'limit' => 3,
            'lock' => true,
        ]);

        // Make sure we received the correct sanitized results
        $this->assertSame($resultsProcessed, $results);
    }

    public function testFlattenedFields(): void
    {
        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.open' => 'ticket.ticket_open',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => 77,
                'ticket.ticket_open' => 0,
            ],
            'limit' => 3,
            'lock' => true,
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'ticket.ticketId' => '54',
                'ticket.open' => '1',
            ],
            [
                'ticket.ticketId' => '33',
                'ticket.open' => '0',
            ],
            [
                'ticket.ticketId' => '89',
                'ticket.open' => '1',
            ],
        ];

        // After the data was processed according to types
        $resultsProcessed = [
            54,
            true,
            33,
            false,
            89,
            true,
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        // Attempt select
        $results = $this->queryHandler->fetchAllAndFlatten([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.open',
            ],
            'where' => [
                'ticket.ticketId' => '77',
                'ticket.open' => false,
            ],
            'limit' => 3,
            'lock' => true,
        ]);

        // Make sure we received the correct sanitized results
        $this->assertSame($resultsProcessed, $results);
    }

    public function testFlattenedFieldsLegacy(): void
    {
        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.open' => 'ticket.ticket_open',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => 77,
                'ticket.ticket_open' => 0,
            ],
            'limit' => 3,
            'lock' => true,
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'ticket.ticketId' => '54',
                'ticket.open' => '1',
            ],
            [
                'ticket.ticketId' => '33',
                'ticket.open' => '0',
            ],
            [
                'ticket.ticketId' => '89',
                'ticket.open' => '1',
            ],
        ];

        // After the data was processed according to types
        $resultsProcessed = [
            54,
            true,
            33,
            false,
            89,
            true,
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        // Attempt select
        $results = $this->queryHandler->fetchAllAndFlatten([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.open',
            ],
            'where' => [
                'ticket.ticketId' => '77',
                'ticket.open' => false,
            ],
            'limit' => 3,
            'lock' => true,
        ]);

        // Make sure we received the correct sanitized results
        $this->assertSame($resultsProcessed, $results);
    }

    public function testCountQuery(): void
    {
        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'COUNT(*) AS "count"',
            ],
            'tables' => [
                'databasename.tickets ticket',
            ],
            'where' => [
                'ticket.ticket_id' => 77,
                'ticket.ticket_open' => 0,
            ],
            'lock' => true,
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'count' => '54',
            ],
        ];

        // After the data was processed according to types
        $resultsProcessed = [
            54,
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        // Attempt select
        $results = $this->queryHandler->fetchAllAndFlatten([
            'repositories' => [
                'ticket' => $this->ticketRepository,
            ],
            'fields' => [
                'count' => 'COUNT(*)',
            ],
            'where' => [
                'ticket.ticketId' => '77',
                'ticket.open' => false,
            ],
            'lock' => true,
        ]);

        // Make sure we received the correct sanitized results
        $this->assertSame($resultsProcessed, $results);
    }

    public function testComplicatedQuery(): void
    {
        // Default database results
        $dbResults = [
            [
                'ticket.ticketId' => '5',
                'ticket.floaty' => '3.78',
                'emailTo' => 'test@example.com',
                'ticket.open' => '1',
                'ticketTitle' => 'First ticket',
                'ticketTitleLength' => '8',
                'updateMinusCreated' => '5',
                'floaty' => '9.5',
                'booleany' => '1',
                'integery' => '1',
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => '53',
                'ticket.floaty' => '8.3',
                'emailTo' => 'test55@example.com',
                'ticket.open' => '0',
                'ticketTitle' => 'Second ticket',
                'ticketTitleLength' => '9',
                'updateMinusCreated' => '8',
                'floaty' => '2022-05-04',
                'booleany' => '0',
                'integery' => '5',
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => '193',
                'ticket.floaty' => '11',
                'emailTo' => 'test@muster.de',
                'ticket.open' => '1',
                'ticketTitle' => 'Third ticket',
                'ticketTitleLength' => '10',
                'updateMinusCreated' => '53',
                'floaty' => '3.3',
                'booleany' => '1',
                'integery' => '1.5',
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => '193',
                'ticket.floaty' => '11',
                'emailTo' => 'test@muster.de',
                'ticket.open' => '1',
                'ticketTitle' => 'Third ticket',
                'ticketTitleLength' => '10',
                'updateMinusCreated' => '53',
                'floaty' => '3.3',
                'booleany' => 'ladida',
                'integery' => '1.5',
                'updateCreatedConcat' => '5',
            ],
        ];

        // The processed database results
        $dbResultsSanitized = [
            [
                'ticket.ticketId' => 5,
                'ticket.floaty' => 3.78,
                'emailTo' => 'test@example.com',
                'ticket.open' => true,
                'ticketTitle' => 'First ticket',
                'ticketTitleLength' => '8',
                'updateMinusCreated' => 5,
                'floaty' => 9.5,
                'booleany' => true,
                'integery' => 1,
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => 53,
                'ticket.floaty' => 8.3,
                'emailTo' => 'test55@example.com',
                'ticket.open' => false,
                'ticketTitle' => 'Second ticket',
                'ticketTitleLength' => '9',
                'updateMinusCreated' => 8,
                'floaty' => '2022-05-04',
                'booleany' => false,
                'integery' => 5,
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => 193,
                'ticket.floaty' => 11.0,
                'emailTo' => 'test@muster.de',
                'ticket.open' => true,
                'ticketTitle' => 'Third ticket',
                'ticketTitleLength' => '10',
                'updateMinusCreated' => 53,
                'floaty' => 3.3,
                'booleany' => true,
                'integery' => '1.5',
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => 193,
                'ticket.floaty' => 11.0,
                'emailTo' => 'test@muster.de',
                'ticket.open' => true,
                'ticketTitle' => 'Third ticket',
                'ticketTitleLength' => '10',
                'updateMinusCreated' => 53,
                'floaty' => 3.3,
                'booleany' => 'ladida',
                'integery' => '1.5',
                'updateCreatedConcat' => '5',
            ],
        ];

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.floaty' => 'ticket.ticket_floaty',
                'emailTo' => 'email.to_address',
                'ticket.open' => 'ticket.ticket_open',
                'ticketTitle' => 'ticket.ticket_title',
                '(LENGTH(' . $this->db->quoteIdentifier('ticket.ticket_title') . ')) AS ' .
                $this->db->quoteIdentifier('ticketTitleLength'),
                '(' . $this->db->quoteIdentifier('ticket.last_update') . '-' .
                $this->db->quoteIdentifier('ticket.create_date') . ') AS ' .
                $this->db->quoteIdentifier('updateMinusCreated'),
                '(' . $this->db->quoteIdentifier('ticket.ticket_floaty') . ') AS ' .
                $this->db->quoteIdentifier('floaty'),
                '(' . $this->db->quoteIdentifier('ticket.ticket_open') . ') AS ' .
                $this->db->quoteIdentifier('booleany'),
                '(' . $this->db->quoteIdentifier('ticket.last_update') . '/2) AS ' .
                $this->db->quoteIdentifier('integery'),
                '(CONCAT(' . $this->db->quoteIdentifier('ticket.last_update') . ',' .
                $this->db->quoteIdentifier('ticket.create_date') . ')) AS ' .
                $this->db->quoteIdentifier('updateCreatedConcat'),
            ],
            'tables' => [
                'databasename.tickets ticket',
                $this->db->quoteIdentifier('tickets_messages') . ' ' . $this->db->quoteIdentifier('message') .
                ' LEFT JOIN ' . $this->db->quoteIdentifier('db74.emails') . ' ' . $this->db->quoteIdentifier('email') .
                ' ON (' . $this->db->quoteIdentifier('message.email_id') . ' = ' .
                $this->db->quoteIdentifier('email.email_id') . ' AND ' .
                $this->db->quoteIdentifier('email.automatic') . ' = ?)' => true,
                $this->db->quoteIdentifier('tickets_messages') . ' ' . $this->db->quoteIdentifier('message') .
                ' LEFT JOIN ' . $this->db->quoteIdentifier('db74.emails') . ' ' . $this->db->quoteIdentifier('email') .
                ' ON (' . $this->db->quoteIdentifier('message.email_id') . ' = ' .
                $this->db->quoteIdentifier('email.email_id') . ')',
            ],
            'where' => [
                $this->db->quoteIdentifier('ticket.ticket_id') . ' = ' .
                $this->db->quoteIdentifier('message.ticket_id'),
                'email.to_address' => 'info@dada.com',
                $this->db->quoteIdentifier('ticket.ticket_open') . ' = ?' => 1,
                $this->db->quoteIdentifier('ticket.ticket_floaty') . ' BETWEEN ? AND ?' => [5.5, 9.5],
            ],
            'group' => [
                'ticket.ticket_id',
                'email.to_address',
                'DATE(' . $this->db->quoteIdentifier('ticket.last_update') . ')',
            ],
            'order' => [
                'ticket.ticket_id' => 'DESC',
                'updateMinusCreated',
                '(:ticket.last_update:-:ticket.create_date:)' => 'DESC',
                ':ticket.last_update:+:ticket.create_date:' => 'ASC',
            ],
            'limit' => 30,
            'offset' => 7,
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($dbResults);

        // Attempt select
        $results = $this->queryHandler->fetchAll($this->complicatedQuery);

        // Make sure we received the correct sanitized results
        $this->assertSame($dbResultsSanitized, $results);
    }

    public function testFreeform(): void
    {
        // Default database results
        $dbResults = [
            [
                'ticket.ticketId' => '5',
                'ticket.floaty' => '3.78',
                'emailTo' => 'test@example.com',
                'ticket.open' => '1',
                'ticketTitle' => 'First ticket',
                'updateMinusCreated' => '5',
                'floaty' => '9.5',
                'booleany' => '1',
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => '53',
                'ticket.floaty' => '8.3',
                'emailTo' => 'test55@example.com',
                'ticket.open' => '0',
                'ticketTitle' => 'Second ticket',
                'updateMinusCreated' => '8',
                'floaty' => '7',
                'booleany' => '0',
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => '193',
                'ticket.floaty' => '11',
                'emailTo' => 'test@muster.de',
                'ticket.open' => '1',
                'ticketTitle' => 'Third ticket',
                'updateMinusCreated' => '53',
                'floaty' => '3.3',
                'booleany' => '1',
                'updateCreatedConcat' => '5',
            ],
        ];

        // The processed database results
        $dbResultsSanitized = [
            [
                'ticket.ticketId' => 5,
                'ticket.floaty' => 3.78,
                'emailTo' => 'test@example.com',
                'ticket.open' => true,
                'ticketTitle' => 'First ticket',
                'updateMinusCreated' => 5,
                'floaty' => 9.5,
                'booleany' => true,
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => 53,
                'ticket.floaty' => 8.3,
                'emailTo' => 'test55@example.com',
                'ticket.open' => false,
                'ticketTitle' => 'Second ticket',
                'updateMinusCreated' => 8,
                'floaty' => 7.0,
                'booleany' => false,
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => 193,
                'ticket.floaty' => 11.0,
                'emailTo' => 'test@muster.de',
                'ticket.open' => true,
                'ticketTitle' => 'Third ticket',
                'updateMinusCreated' => 53,
                'floaty' => 3.3,
                'booleany' => true,
                'updateCreatedConcat' => '5',
            ],
        ];

        // Freeform query parts
        $queryFreeform = [
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'emailTo' => 'email.to',
                'ticket.open',
                'ticketTitle' => 'ticket.title',
                'updateMinusCreated' => ':ticket.lastUpdate:-:ticket.createDate:',
                'floaty' => ':ticket.floaty:',
                'booleany' => ':ticket.open:',
                'updateCreatedConcat' => 'CONCAT(:ticket.lastUpdate:,:ticket.createDate:)',
            ],
            'query' => ':ticket:,:message: LEFT JOIN :email: ' .
                'ON (:message.emailId: = :email.emailId: AND :email.automatic: = ?) ' .
                'WHERE (:ticket.ticketId: = :message.ticketId:) AND (:ticket.open: = ?) AND (:ticket.floaty: = ?) ' .
                'GROUP BY :ticket.ticketId: ' .
                'ORDER BY :ticket.ticketId: DESC,' .
                'updateMinusCreated ASC,' .
                '(:ticket.lastUpdate:-:ticket.createDate:) DESC ' .
                'LIMIT 30',
            'parameters' => [
                true,
                true,
                9.5,
            ],
        ];

        // The values we want to receive
        $expectedQuery = 'SELECT ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' AS "ticket.ticketId",' .
            $this->db->quoteIdentifier('ticket.ticket_floaty') . ' AS "ticket.floaty",' .
            $this->db->quoteIdentifier('email.to_address') . ' AS "emailTo",' .
            $this->db->quoteIdentifier('ticket.ticket_open') . ' AS "ticket.open",' .
            $this->db->quoteIdentifier('ticket.ticket_title') . ' AS "ticketTitle",' .
            '(' . $this->db->quoteIdentifier('ticket.last_update') . '-' .
            $this->db->quoteIdentifier('ticket.create_date') . ') AS "updateMinusCreated",' .
            '(' . $this->db->quoteIdentifier('ticket.ticket_floaty') . ') AS "floaty",' .
            '(' . $this->db->quoteIdentifier('ticket.ticket_open') . ') AS "booleany",' .
            '(CONCAT(' . $this->db->quoteIdentifier('ticket.last_update') . ',' .
            $this->db->quoteIdentifier('ticket.create_date') . ')) AS "updateCreatedConcat" ' .
            'FROM ' . $this->db->quoteIdentifier('databasename.tickets') . ' ' .
            $this->db->quoteIdentifier('ticket') . ',' .
            $this->db->quoteIdentifier('tickets_messages') . ' ' . $this->db->quoteIdentifier('message') .
            ' LEFT JOIN ' . $this->db->quoteIdentifier('db74.emails') . ' ' . $this->db->quoteIdentifier('email') .
            ' ON (' . $this->db->quoteIdentifier('message.email_id') . ' = ' .
            $this->db->quoteIdentifier('email.email_id') . ' AND ' .
            $this->db->quoteIdentifier('email.automatic') . ' = ?) ' .
            'WHERE (' . $this->db->quoteIdentifier('ticket.ticket_id') . ' = ' .
            $this->db->quoteIdentifier('message.ticket_id') . ') ' .
            'AND (' . $this->db->quoteIdentifier('ticket.ticket_open') . ' = ?) ' .
            'AND (' . $this->db->quoteIdentifier('ticket.ticket_floaty') . ' = ?) ' .
            'GROUP BY ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' ' .
            'ORDER BY ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' DESC,' .
            'updateMinusCreated ASC,' .
            '(' . $this->db->quoteIdentifier('ticket.last_update') . '-' .
            $this->db->quoteIdentifier('ticket.create_date') . ') DESC ' .
            'LIMIT 30';
        $values = [1, 1, 9.5];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with(IsEqual::equalTo($expectedQuery), IsEqual::equalTo($values))
            ->andReturn($dbResults);

        // Attempt select
        $results = $this->queryHandler->fetchAll($queryFreeform);

        // Make sure we received the correct sanitized results
        $this->assertSame($dbResultsSanitized, $results);
    }

    public function testFreeformOneField(): void
    {
        // Default database results
        $dbResults = [
            [
                'ticket.ticketId' => '54',
            ],
            [
                'ticket.ticketId' => '33',
            ],
            [
                'ticket.ticketId' => '89',
            ],
        ];

        // The processed database results
        $dbResultsSanitized = [
            54,
            33,
            89,
        ];

        // The values we want to receive
        $expectedQuery = 'SELECT ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' AS "ticket.ticketId" ' .
            'FROM ' . $this->db->quoteIdentifier('databasename.tickets') . ' ' . $this->db->quoteIdentifier('ticket') .
            ',' . $this->db->quoteIdentifier('tickets_messages') . ' ' . $this->db->quoteIdentifier('message') .
            ' LEFT JOIN ' . $this->db->quoteIdentifier('db74.emails') . ' ' . $this->db->quoteIdentifier('email') .
            ' ON (' . $this->db->quoteIdentifier('message.email_id') . ' = ' .
            $this->db->quoteIdentifier('email.email_id') .
            ' AND ' . $this->db->quoteIdentifier('email.automatic') . ' = ?) ' .
            'WHERE (' . $this->db->quoteIdentifier('ticket.ticket_id') . ' = ' .
            $this->db->quoteIdentifier('message.ticket_id') . ') AND (' .
            $this->db->quoteIdentifier('ticket.ticket_open') . ' = ?) ' .
            'AND (' . $this->db->quoteIdentifier('ticket.ticket_floaty') . ' = ?) ' .
            'GROUP BY ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' ' .
            'ORDER BY ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' DESC,' .
            'updateMinusCreated ASC,' .
            '(' . $this->db->quoteIdentifier('ticket.last_update') . '-' .
            $this->db->quoteIdentifier('ticket.create_date') . ') DESC ' .
            'LIMIT 30';
        $values = [1, 1, 9.5];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with(IsEqual::equalTo($expectedQuery), IsEqual::equalTo($values))
            ->andReturn($dbResults);

        // Attempt select
        $results = $this->queryHandler->fetchAllAndFlatten($this->queryFreeform);

        // Make sure we received the correct sanitized results
        $this->assertSame($dbResultsSanitized, $results);
    }

    public function testUnrecognizedOption(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid WHERE value
        $this->complicatedQuery['unrecognized'] = [
            'ticket.ticketId' => 5,
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidFieldsValue(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid fields value
        $this->complicatedQuery['fields'] = 'ticket';

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidWhere1Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid WHERE value
        $this->complicatedQuery['where'] = [
            'ticket.ticketIdUndefined' => 5,
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidWhere2Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid WHERE value
        $this->complicatedQuery['where'] = [
            ':ticket.ticketIdUndefined: = :message.ticketId:',
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidWhere3Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid WHERE value
        $this->complicatedQuery['where'] = [
            1 => 5,
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidWhere4Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid WHERE value
        $this->complicatedQuery['where'] = [];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidRepositories1Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid repositories definition
        $this->complicatedQuery['repositories'] = [];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidRepositories2Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid SELECT value
        $this->complicatedQuery['repositories'] = [
            5,
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidRepositories3Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid SELECT value
        $this->complicatedQuery['repositories']['email'] = new \stdClass();

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidFields1Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid SELECT value
        $this->complicatedQuery['fields'] = [];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidFields2Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid SELECT value
        $this->complicatedQuery['fields'] = [
            'ticket.ticketIdInvalid',
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidFields3Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid SELECT value
        $this->complicatedQuery['fields'] = [
            5,
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidFields4Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid SELECT value
        $this->complicatedQuery['fields'] = [
            'email' => 5,
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidFields5Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid SELECT value
        $this->complicatedQuery['fields'] = [
            ':email' => 'ticket.ticketId',
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidFields6Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid SELECT value
        $this->complicatedQuery['fields'] = [
            'email' => ':ticket.ticketIdInvalid:',
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidTables1Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid FROM value
        $this->complicatedQuery['tables'] = [
            'invalidTable',
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidTables2Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid FROM value
        $this->complicatedQuery['tables'] = [
            ':invalidTableExpression:',
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidTables3Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid FROM value
        $this->complicatedQuery['tables'] = [
            0,
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidGroup1Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid GROUP value
        $this->complicatedQuery['group'] = [
            'ticket.ticketIdInvalid',
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidGroup2Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid GROUP value
        $this->complicatedQuery['group'] = [
            new \stdClass(),
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidGroup3Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid GROUP value
        $this->complicatedQuery['group'] = [
            'DATE(:ticket.doesnotexist:)',
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidOrder1Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid ORDER value
        $this->complicatedQuery['order'] = [
            ':ticket.ticketIdInvalid:',
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testInvalidOrder2Value(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid ORDER value
        $this->complicatedQuery['order'] = [
            new \stdClass(),
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testFetchOneInvalidLimit(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Try to test with some invalid ORDER value
        $this->complicatedQuery['limit'] = 5;

        // Attempt select
        $this->queryHandler->fetchOne($this->complicatedQuery);
    }

    public function testInvalidRepositoryFieldType(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Ticket repository - mocked
        $ticketRepositoryConfig = new RepositoryConfig('', 'databasename.tickets', [
            'ticket_id' => 'ticketId',
            'ticket_title' => 'title',
            'ticket_floaty' => 'floaty',
            'ticket_open' => 'open',
            'ticket_status' => 'status',
            'msgNumber' => 'messagesNumber',
            'last_update' => 'lastUpdate',
            'create_date' => 'createDate',
        ], [
            'ticketId' => 'ticket_id',
            'title' => 'ticket_title',
            'floaty' => 'ticket_floaty',
            'open' => 'ticket_open',
            'status' => 'ticket_status',
            'messagesNumber' => 'msgNumber',
            'lastUpdate' => 'last_update',
            'createDate' => 'create_date',
        ], 'ObjectClass', [
            'ticketId' => 'int',
            'title' => 'fantasyType', // invalid value!
            'floaty' => 'float',
            'open' => 'bool',
            'status' => 'int',
            'messagesNumber' => 'int',
            'lastUpdate' => 'int',
            'createDate' => 'int',
        ], [
            'ticketId' => false,
            'title' => false,
            'floaty' => false,
            'open' => false,
            'status' => false,
            'messagesNumber' => false,
            'lastUpdate' => true,
            'createDate' => false,
        ]);

        $ticketRepository = new TestClasses\TicketRepositoryBuilderReadOnly(
            new RepositoryReadOnly($this->db, $ticketRepositoryConfig),
        );

        $this->complicatedQuery['repositories'] = [
            'ticket' => $ticketRepository,
            'message' => $this->ticketMessageRepository,
            'email' => $this->emailRepository,
        ];

        // Default database results
        $dbResults = [
            [
                'ticket.ticketId' => '5',
                'ticket.floaty' => '3.78',
                'emailTo' => 'test@example.com',
                'ticket.open' => '1',
                'ticketTitle' => 'First ticket',
                'ticketTitleLength' => '8',
                'updateMinusCreated' => '5',
                'floaty' => '9.5',
                'booleany' => '1',
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => '53',
                'ticket.floaty' => '8.3',
                'emailTo' => 'test55@example.com',
                'ticket.open' => '0',
                'ticketTitle' => 'Second ticket',
                'ticketTitleLength' => '9',
                'updateMinusCreated' => '8',
                'floaty' => '7',
                'booleany' => '0',
                'updateCreatedConcat' => '5',
            ],
            [
                'ticket.ticketId' => '193',
                'ticket.floaty' => '11',
                'emailTo' => 'test@muster.de',
                'ticket.open' => '1',
                'ticketTitle' => 'Third ticket',
                'ticketTitleLength' => '10',
                'updateMinusCreated' => '53',
                'floaty' => '3.3',
                'booleany' => '1',
                'updateCreatedConcat' => '5',
            ],
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->andReturn($dbResults);

        // Attempt select
        $this->queryHandler->fetchAll($this->complicatedQuery);
    }

    public function testBadRepositoryForReflection(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        $ticketRepository = new TestClasses\TicketRepositoryReadOnlyDifferentRepositoryBuilderVariableWithin(
            new RepositoryReadOnly($this->db, $this->ticketRepositoryConfig),
        );

        $this->complicatedQuery['repositories'] = [
            'ticket' => $ticketRepository,
            'message' => $this->ticketMessageRepository,
            'email' => $this->emailRepository,
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testBadRepositoryForReflection2(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        $ticketMessageRepository = new TestClasses\TicketMessageRepositoryReadOnlyDifferentRepositoryVariableWithin();

        $this->complicatedQuery['repositories'] = [
            'ticket' => $this->ticketRepository,
            'message' => $ticketMessageRepository,
            'email' => $this->emailRepository,
        ];

        // Attempt select
        $this->queryHandler->select($this->complicatedQuery);
    }

    public function testUnresolvedFreeform(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        $this->queryFreeform['query'] = $this->queryFreeform['query'] . ' :invalid:';

        // Attempt select
        $this->queryHandler->select($this->queryFreeform);
    }

    public function testFreeformNoFields(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // Attempt select
        $this->queryHandler->fetchAll([
            'repositories' => $this->queryFreeform['repositories'],
            'fields' => [],
            'query' => $this->queryFreeform['query'],
            'parameters' => $this->queryFreeform['parameters'],
        ]);
    }

    public function testFreeformNoQuery(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        unset($this->queryFreeform['query']);

        // Attempt select
        $this->queryHandler->select($this->queryFreeform);
    }

    public function testFreeformNoTables(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        unset($this->queryFreeform['repositories']);

        // Attempt select
        $this->queryHandler->select($this->queryFreeform);
    }

    public function testFreeformBadParameter(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        $this->queryFreeform['parameters'] = [new \stdClass()];

        // Attempt select
        $this->queryHandler->select($this->queryFreeform);
    }

    public function testSelectExceptionFromDbClass(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.floaty' => 'ticket.ticket_floaty',
                'ticket.open' => 'ticket.ticket_open',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => [77, 88, 193],
                'ticket.ticket_open' => 1,
            ],
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($expectedQuery, [])
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Attempt select
        $this->queryHandler->select([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'ticket.open',
            ],
            'where' => [
                'ticket.ticketId' => [77, 88, 193],
                'ticket.open' => true,
            ],
        ]);
    }

    public function testFetchExceptionFromDbClass(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.floaty' => 'ticket.ticket_floaty',
                'ticket.open' => 'ticket.ticket_open',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => [77, 88, 193],
                'ticket.ticket_open' => 1,
            ],
        ];

        $dbSelectQuery = \Mockery::mock(DBSelectQueryInterface::class);

        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($dbSelectQuery);
        $this->db
            ->shouldReceive('fetch')
            ->once()
            ->with($dbSelectQuery)
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Attempt select
        $queryResult = $this->queryHandler->select([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'ticket.open',
            ],
            'where' => [
                'ticket.ticketId' => [77, 88, 193],
                'ticket.open' => true,
            ],
        ]);

        $this->queryHandler->fetch($queryResult);
    }

    public function testClearExceptionFromDbClass(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.floaty' => 'ticket.ticket_floaty',
                'ticket.open' => 'ticket.ticket_open',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => [77, 88, 193],
                'ticket.ticket_open' => 1,
            ],
        ];

        $dbSelectQuery = \Mockery::mock(DBSelectQueryInterface::class);

        $this->db
            ->shouldReceive('select')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($dbSelectQuery);
        $this->db
            ->shouldReceive('clear')
            ->once()
            ->with($dbSelectQuery)
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Attempt select
        $queryResult = $this->queryHandler->select([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'ticket.open',
            ],
            'where' => [
                'ticket.ticketId' => [77, 88, 193],
                'ticket.open' => true,
            ],
        ]);

        $this->queryHandler->clear($queryResult);
    }

    public function testFetchAllExceptionFromDbClass(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.floaty' => 'ticket.ticket_floaty',
                'ticket.open' => 'ticket.ticket_open',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => [77, 88, 193],
                'ticket.ticket_open' => 1,
            ],
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Attempt select
        $this->queryHandler->fetchAll([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'ticket.open',
            ],
            'where' => [
                'ticket.ticketId' => [77, 88, 193],
                'ticket.open' => true,
            ],
        ]);
    }

    public function testFreeformOneFieldExceptionFromDbClass(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // The values we want to receive
        $expectedQuery = 'SELECT ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' AS "ticket.ticketId" ' .
            'FROM ' . $this->db->quoteIdentifier('databasename.tickets') . ' ' . $this->db->quoteIdentifier('ticket') .
            ',' . $this->db->quoteIdentifier('tickets_messages') . ' ' . $this->db->quoteIdentifier('message') .
            ' LEFT JOIN ' . $this->db->quoteIdentifier('db74.emails') . ' ' . $this->db->quoteIdentifier('email') .
            ' ON (' . $this->db->quoteIdentifier('message.email_id') . ' = ' .
            $this->db->quoteIdentifier('email.email_id') . ' AND ' .
            $this->db->quoteIdentifier('email.automatic') . ' = ?) ' .
            'WHERE (' . $this->db->quoteIdentifier('ticket.ticket_id') . ' = ' .
            $this->db->quoteIdentifier('message.ticket_id') . ') ' .
            'AND (' . $this->db->quoteIdentifier('ticket.ticket_open') . ' = ?) ' .
            'AND (' . $this->db->quoteIdentifier('ticket.ticket_floaty') . ' = ?) ' .
            'GROUP BY ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' ' .
            'ORDER BY ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' DESC,' .
            'updateMinusCreated ASC,' .
            '(' . $this->db->quoteIdentifier('ticket.last_update') . '-' .
            $this->db->quoteIdentifier('ticket.create_date') . ') DESC ' .
            'LIMIT 30';
        $values = [1, 1, 9.5];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with(IsEqual::equalTo($expectedQuery), IsEqual::equalTo($values))
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Attempt select
        $this->queryHandler->fetchAll($this->queryFreeform);
    }

    public function testRepositoriesWithTheSameConnection(): void
    {
        $ticketRepository = new TestClasses\TicketRepositoryBuilderReadOnly(
            new RepositoryReadOnly($this->db, $this->ticketRepositoryConfig),
        );

        $ticketRepository2 = new TestClasses\TicketRepositoryBuilderReadOnly(
            new RepositoryReadOnly($this->db, $this->ticketRepositoryConfig),
        );

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket2.ticketId' => 'ticket2.ticket_id',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'databasename.tickets ticket2',
            ],
            'where' => [
                $this->db->quoteIdentifier('ticket.ticket_id') . ' = ' .
                $this->db->quoteIdentifier('ticket2.ticket_id'),
                'ticket.ticket_id' => 77,
            ],
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'ticket.ticketId' => '54',
                'ticket2.ticketId' => '54',
            ],
            [
                'ticket.ticketId' => '33',
                'ticket2.ticketId' => '33',
            ],
            [
                'ticket.ticketId' => '89',
                'ticket2.ticketId' => '89',
            ],
        ];

        // After the data was processed according to types
        $resultsProcessed = [
            [
                'ticket.ticketId' => 54,
                'ticket2.ticketId' => 54,
            ],
            [
                'ticket.ticketId' => 33,
                'ticket2.ticketId' => 33,
            ],
            [
                'ticket.ticketId' => 89,
                'ticket2.ticketId' => 89,
            ],
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        $results = $this->queryHandler->fetchAll([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'ticket2' => $ticketRepository2,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket2.ticketId',
            ],
            'where' => [
                ':ticket.ticketId: = :ticket2.ticketId:',
                'ticket.ticketId' => '77',
            ],
        ]);

        // Make sure we received the correct sanitized results
        $this->assertSame($resultsProcessed, $results);
    }

    public function testBuilderRepositoriesWithDifferentConnections(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        $db2 = \Mockery::mock(DBInterface::class)->makePartial();

        $ticketRepository = new TestClasses\TicketRepositoryBuilderReadOnly(
            new RepositoryReadOnly($this->db, $this->ticketRepositoryConfig),
        );

        $ticketRepository2 = new TestClasses\TicketRepositoryBuilderReadOnly(
            new RepositoryReadOnly($db2, $this->ticketRepositoryConfig),
        );

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket2.ticketId' => 'ticket2.ticket_id',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'databasename.tickets ticket2',
            ],
            'where' => [
                $this->db->quoteIdentifier('ticket.ticket_id') . ' = ' .
                $this->db->quoteIdentifier('ticket2.ticket_id'),
                'ticket.ticket_id' => 77,
            ],
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'ticket.ticketId' => '54',
                'ticket2.ticketId' => '54',
            ],
            [
                'ticket.ticketId' => '33',
                'ticket2.ticketId' => '33',
            ],
            [
                'ticket.ticketId' => '89',
                'ticket2.ticketId' => '89',
            ],
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        // Attempt select
        $this->queryHandler->fetchAll([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'ticket2' => $ticketRepository2,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket2.ticketId',
            ],
            'where' => [
                ':ticket.ticketId: = :ticket2.ticketId:',
                'ticket.ticketId' => '77',
            ],
        ]);
    }

    public function testBaseRepositoriesWithDifferentConnections(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        $db2 = \Mockery::mock(DBInterface::class)->makePartial();

        $ticketRepository = new RepositoryReadOnly($this->db, $this->ticketRepositoryConfig);

        $ticketRepository2 = new RepositoryReadOnly($db2, $this->ticketRepositoryConfig);

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket2.ticketId' => 'ticket2.ticket_id',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'databasename.tickets ticket2',
            ],
            'where' => [
                $this->db->quoteIdentifier('ticket.ticket_id') . ' = ' .
                $this->db->quoteIdentifier('ticket2.ticket_id'),
                'ticket.ticket_id' => 77,
            ],
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'ticket.ticketId' => '54',
                'ticket2.ticketId' => '54',
            ],
            [
                'ticket.ticketId' => '33',
                'ticket2.ticketId' => '33',
            ],
            [
                'ticket.ticketId' => '89',
                'ticket2.ticketId' => '89',
            ],
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        // Attempt select
        $this->queryHandler->fetchAll([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'ticket2' => $ticketRepository2,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket2.ticketId',
            ],
            'where' => [
                ':ticket.ticketId: = :ticket2.ticketId:',
                'ticket.ticketId' => '77',
            ],
        ]);
    }

    public function testRepositoryWithInvalidConnection(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        $ticketRepository = new TicketRepositoryReadOnlyCorrectNameButInvalidDatabaseConnection(
            $this->ticketRepositoryConfig,
        );

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
            ],
            'tables' => [
                'databasename.tickets ticket',
            ],
            'where' => [
                'ticket.ticket_id' => 77,
            ],
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'ticket.ticketId' => '54',
            ],
            [
                'ticket.ticketId' => '33',
            ],
            [
                'ticket.ticketId' => '89',
            ],
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        // Attempt select
        $this->queryHandler->fetchAll([
            'repositories' => [
                'ticket' => $ticketRepository,
            ],
            'fields' => [
                'ticket.ticketId',
            ],
            'where' => [
                'ticket.ticketId' => '77',
            ],
        ]);
    }

    public function testTypeCoercionException(): void
    {
        $this->expectException(DBInvalidOptionException::class);

        // The query we want to receive
        $expectedQuery = [
            'fields' => [
                'ticket.ticketId' => 'ticket.ticket_id',
                'ticket.floaty' => 'ticket.ticket_floaty',
                'ticket.open' => 'ticket.ticket_open',
                'ticket.title' => 'ticket.ticket_title',
                'ticket.lastUpdate' => 'ticket.last_update',
            ],
            'tables' => [
                'databasename.tickets ticket',
                'tickets_messages message',
                'db74.emails email',
            ],
            'where' => [
                'ticket.ticket_id' => [77, 88, 193],
                'ticket.ticket_open' => 1,
            ],
        ];

        // What the database returns
        $resultsFromDb = [
            [
                'ticket.ticketId' => 'hello',
                'ticket.floaty' => 'ladida',
                'ticket.open' => '5',
                'ticket.title' => true,
                'ticket.lastUpdate' => null,
            ],
        ];

        // After the data was processed according to types
        $resultsProcessed = [
            [
                'ticket.ticketId' => 0,
                'ticket.floaty' => 0.0,
                'ticket.open' => true,
                'ticket.title' => '1',
                'ticket.lastUpdate' => null,
            ],
        ];

        // Fetching results - return the stored results
        $this->db
            ->shouldReceive('fetchAll')
            ->once()
            ->with($expectedQuery, [])
            ->andReturn($resultsFromDb);

        // Attempt select
        $this->queryHandler->fetchAll([
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'fields' => [
                'ticket.ticketId',
                'ticket.floaty',
                'ticket.open',
                'ticket.title',
                'ticket.lastUpdate',
            ],
            'where' => [
                'ticket.ticketId' => [77, 88, 193],
                'ticket.open' => true,
            ],
        ]);
    }
}
