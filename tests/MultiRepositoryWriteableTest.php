<?php

namespace Squirrel\Entities\Tests;

use Squirrel\Entities\MultiRepositoryWriteable;
use Squirrel\Entities\RepositoryConfig;
use Squirrel\Entities\RepositoryConfigInterface;
use Squirrel\Entities\RepositoryReadOnly;
use Squirrel\Entities\RepositoryWriteable;
use Squirrel\Entities\Tests\TestClasses\TicketRepositoryBuilderReadOnly;
use Squirrel\Queries\Exception\DBInvalidOptionException;
use Squirrel\Queries\TestHelpers\DBInterfaceForTests;

/**
 * We especially test all the arguments for validity in these test cases, in addition
 * to the main regular parts of QueryHandler for UPDATE queries
 */
class MultiRepositoryWriteableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected $query;

    /**
     * @var array
     */
    protected $queryFreeform;

    /**
     * @var MultiRepositoryWriteable
     */
    protected $queryHandler;

    /**
     * @var DBInterfaceForTests
     */
    protected $db;

    /**
     * @var RepositoryConfigInterface
     */
    protected $ticketRepositoryConfig;

    /**
     * @var \Squirrel\Entities\Tests\TestClasses\TicketRepositoryBuilderWriteable
     */
    protected $ticketRepository;

    /**
     * @var RepositoryConfigInterface
     */
    protected $ticketMessageRepositoryConfig;

    /**
     * @var RepositoryReadOnly
     */
    protected $ticketMessageRepository;

    /**
     * @var RepositoryConfigInterface
     */
    protected $emailRepository;

    /**
     * @var array
     */
    protected $dbResults;

    /**
     * @var array
     */
    protected $dbResultsSanitized;

    /**
     * Initialize for every test in this class
     */
    protected function setUp(): void
    {
        // Mock Database class
        $this->db = \Mockery::mock(DBInterfaceForTests::class)->makePartial();

        // Initialize query handler so it can be used
        $this->queryHandler = new MultiRepositoryWriteable();

        // Ticket repository - mocked
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
            'lastUpdate' => false,
            'createDate' => false,
        ]);

        $this->ticketRepository = new TestClasses\TicketRepositoryBuilderWriteable(
            new RepositoryWriteable(
                $this->db,
                $this->ticketRepositoryConfig
            )
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

        $this->ticketMessageRepository = new RepositoryWriteable($this->db, $this->ticketMessageRepositoryConfig);

        $this->emailRepository = new RepositoryWriteable($this->db, new RepositoryConfig(
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
            ]
        ));

        // Default query which is manipulated by all the tests - freeform variant
        $this->queryFreeform = [
            'repositories' => [
                'ticket' => $this->ticketRepository,
                'message' => $this->ticketMessageRepository,
                'email' => $this->emailRepository,
            ],
            'query' => 'UPDATE :ticket:,:message: ' .
                'LEFT JOIN :email: ON (:message.emailId: = :email.emailId: AND :email.automatic: = ?) ' .
                'SET :ticket.lastUpdate:=?,:ticket.messagesNumber: = ? ' .
                'WHERE (:ticket.ticketId: = :message.ticketId:) ' .
                'ORDER BY ' .
                ':ticket.ticketId: DESC,' .
                'updateMinusCreated ASC,' .
                '(:ticket.lastUpdate:-:ticket.createDate:) DESC ' .
                'LIMIT 30',
            'parameters' => [
                true,
                5,
                13,
            ],
        ];
    }

    public function testFreeform()
    {
        // The values we want to receive
        $expectedQuery = 'UPDATE ' .
            $this->db->quoteIdentifier('databasename.tickets') . ' ' . $this->db->quoteIdentifier('ticket') . ',' .
            $this->db->quoteIdentifier('tickets_messages') . ' ' . $this->db->quoteIdentifier('message') .
            ' LEFT JOIN ' . $this->db->quoteIdentifier('db74.emails') . ' ' . $this->db->quoteIdentifier('email') .
            ' ON (' . $this->db->quoteIdentifier('message.email_id') . ' = ' .
            $this->db->quoteIdentifier('email.email_id') . ' AND ' .
            $this->db->quoteIdentifier('email.automatic') . ' = ?) ' .
            'SET ' . $this->db->quoteIdentifier('ticket.last_update') . '=?,' .
            $this->db->quoteIdentifier('ticket.msgNumber') . ' = ? ' .
            'WHERE (' . $this->db->quoteIdentifier('ticket.ticket_id') . ' = ' .
            $this->db->quoteIdentifier('message.ticket_id') . ') ' .
            'ORDER BY ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' DESC,' .
            'updateMinusCreated ASC,' .
            '(' . $this->db->quoteIdentifier('ticket.last_update') .
            '-' . $this->db->quoteIdentifier('ticket.create_date') . ') ' .
            'DESC LIMIT 30';
        $values = [1, 5, 13];

        // Catch the call to the database
        $this->db
            ->shouldReceive('change')
            ->once()
            ->with(\Mockery::mustBe($expectedQuery), \Mockery::mustBe($values))
            ->andReturn(13);

        // Attempt update
        $results = $this->queryHandler->update(
            $this->queryFreeform['repositories'],
            $this->queryFreeform['query'],
            $this->queryFreeform['parameters']
        );

        // Make sure we received the correct sanitized results
        $this->assertSame(13, $results);
    }

    public function testUnresolvedFreeform()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        $this->queryFreeform['query'] = $this->queryFreeform['query'] . ' :invalid:';

        // Attempt update
        $this->queryHandler->update(
            $this->queryFreeform['repositories'],
            $this->queryFreeform['query'],
            $this->queryFreeform['parameters']
        );
    }

    public function testFreeformNoQuery()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        $this->queryFreeform['query'] = '';

        // Attempt update
        $this->queryHandler->update(
            $this->queryFreeform['repositories'],
            $this->queryFreeform['query'],
            $this->queryFreeform['parameters']
        );
    }

    public function testFreeformNoTables()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        $this->queryFreeform['repositories'] = [];

        // Attempt update
        $this->queryHandler->update(
            $this->queryFreeform['repositories'],
            $this->queryFreeform['query'],
            $this->queryFreeform['parameters']
        );
    }

    public function testNoWriteRepository()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        $ticketRepository = new TicketRepositoryBuilderReadOnly(
            new RepositoryReadOnly($this->db, $this->ticketRepositoryConfig)
        );

        $this->queryFreeform['repositories'] = [
            'ticket' => $ticketRepository,
            'message' => $this->ticketMessageRepository,
            'email' => $this->emailRepository,
        ];

        // Attempt update
        $this->queryHandler->update(
            $this->queryFreeform['repositories'],
            $this->queryFreeform['query'],
            $this->queryFreeform['parameters']
        );
    }

    public function testNoWriteRepository2()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        $ticketMessageRepository = new RepositoryReadOnly($this->db, $this->ticketMessageRepositoryConfig);

        $this->queryFreeform['repositories'] = [
            'ticket' => $this->ticketRepository,
            'message' => $ticketMessageRepository,
            'email' => $this->emailRepository,
        ];

        // Attempt update
        $this->queryHandler->update(
            $this->queryFreeform['repositories'],
            $this->queryFreeform['query'],
            $this->queryFreeform['parameters']
        );
    }

    public function testFreeformExceptionFromDbClass()
    {
        // Expect an invalid option exception
        $this->expectException(DBInvalidOptionException::class);

        // The values we want to receive
        $expectedQuery = 'UPDATE ' .
            $this->db->quoteIdentifier('databasename.tickets') . ' ' . $this->db->quoteIdentifier('ticket') . ',' .
            $this->db->quoteIdentifier('tickets_messages') . ' ' . $this->db->quoteIdentifier('message') .
            ' LEFT JOIN ' . $this->db->quoteIdentifier('db74.emails') . ' ' . $this->db->quoteIdentifier('email') .
            ' ON (' . $this->db->quoteIdentifier('message.email_id') . ' = ' .
            $this->db->quoteIdentifier('email.email_id') . ' AND ' .
            $this->db->quoteIdentifier('email.automatic') . ' = ?) ' .
            'SET ' . $this->db->quoteIdentifier('ticket.last_update') . '=?,' .
            $this->db->quoteIdentifier('ticket.msgNumber') . ' = ? ' .
            'WHERE (' .
            $this->db->quoteIdentifier('ticket.ticket_id') . ' = ' .
            $this->db->quoteIdentifier('message.ticket_id') . ') ' .
            'ORDER BY ' . $this->db->quoteIdentifier('ticket.ticket_id') . ' DESC,' .
            'updateMinusCreated ASC,' .
            '(' . $this->db->quoteIdentifier('ticket.last_update') . '-' .
            $this->db->quoteIdentifier('ticket.create_date') . ') DESC ' .
            'LIMIT 30';
        $values = [1, 5, 13];

        // Catch the call to the database
        $this->db
            ->shouldReceive('change')
            ->once()
            ->with(\Mockery::mustBe($expectedQuery), \Mockery::mustBe($values))
            ->andThrow(new DBInvalidOptionException('dada', 'file', 99, 'message'));

        // Attempt update
        $this->queryHandler->update(
            $this->queryFreeform['repositories'],
            $this->queryFreeform['query'],
            $this->queryFreeform['parameters']
        );
    }
}
