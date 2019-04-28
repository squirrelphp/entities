<?php

namespace Squirrel\Entities;

use Squirrel\Queries\DBSelectQueryInterface;

class RepositorySelectQuery implements RepositorySelectQueryInterface
{
    /**
     * @var DBSelectQueryInterface
     */
    private $selectQuery;

    /**
     * @var RepositoryConfigInterface
     */
    private $repositoryConfig;

    public function __construct(DBSelectQueryInterface $selectQuery, RepositoryConfigInterface $repositoryConfig)
    {
        $this->selectQuery = $selectQuery;
        $this->repositoryConfig = $repositoryConfig;
    }

    public function getQuery(): DBSelectQueryInterface
    {
        return $this->selectQuery;
    }

    public function getConfig(): RepositoryConfigInterface
    {
        return $this->repositoryConfig;
    }
}
