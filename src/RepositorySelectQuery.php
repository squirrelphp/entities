<?php

namespace Squirrel\Entities;

use Squirrel\Queries\DBSelectQueryInterface;

class RepositorySelectQuery implements RepositorySelectQueryInterface
{
    public function __construct(
        private DBSelectQueryInterface $selectQuery,
        private RepositoryConfigInterface $repositoryConfig,
    ) {
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
