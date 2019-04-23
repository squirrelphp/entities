<?php

namespace Squirrel\Entities\Tests\TestClasses;

use Squirrel\Entities\RepositoryConfig;
use Squirrel\Entities\RepositoryConfigInterface;
use Squirrel\Entities\RepositoryReadOnlyInterface;

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
     * @param RepositoryConfig $config
     */
    public function __construct(RepositoryConfig $config)
    {
        $this->db = new \stdClass();
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function select(array $query): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function selectOne(array $query)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function selectFlattenedFields(array $query): array
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
