<?php

namespace Squirrel\Entities;

use Squirrel\Queries\DBSelectQueryInterface;

interface RepositorySelectQueryInterface
{
    /**
     * Get query of the underlying connection
     */
    public function getQuery(): DBSelectQueryInterface;

    /**
     * Repository configuration
     */
    public function getConfig(): RepositoryConfigInterface;
}
