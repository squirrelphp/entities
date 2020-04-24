<?php

namespace Squirrel\Entities;

use Squirrel\Queries\DBSelectQueryInterface;

class RepositorySelectQuery implements RepositorySelectQueryInterface
{
    private DBSelectQueryInterface $selectQuery;
    private RepositoryConfigInterface $repositoryConfig;

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
