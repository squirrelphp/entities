<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\RepositoryConfigInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Entities\RepositorySelectQueryInterface;

class TicketRepositoryReadOnlyCorrectNameButInvalidDatabaseConnection implements RepositoryReadOnlyInterface
{
    /**
     * @var \stdClass
     */
    protected $db;

    /**
     * @var RepositoryConfigInterface
     */
    protected $config;

    /**
     * @param RepositoryConfigInterface $config
     */
    public function __construct(RepositoryConfigInterface $config)
    {
        $this->db = new \stdClass();
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function select(array $query): RepositorySelectQueryInterface
    {
        return \Mockery::mock(RepositorySelectQueryInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function fetch(RepositorySelectQueryInterface $selectQuery)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function clear(RepositorySelectQueryInterface $selectQuery): void
    {
    }

    /**
     * @inheritDoc
     */
    public function fetchOne(array $query)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(array $query)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function count(array $query): int
    {
        return 6;
    }
}
