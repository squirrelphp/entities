<?php

namespace Squirrel\Entities;

use Squirrel\Queries\DBSelectQueryInterface;

interface RepositorySelectQueryInterface
{
    /**
     * Get query of the underlying connection
     *
     * @return DBSelectQueryInterface
     */
    public function getQuery(): DBSelectQueryInterface;

    /**
     * Repository configuration
     *
     * @return RepositoryConfigInterface
     */
    public function getConfig(): RepositoryConfigInterface;
}
