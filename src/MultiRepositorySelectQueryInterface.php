<?php

namespace Squirrel\Entities;

use Squirrel\Queries\DBSelectQueryInterface;

interface MultiRepositorySelectQueryInterface
{
    /**
     * @return DBSelectQueryInterface Get query of the underlying connection
     */
    public function getQuery(): DBSelectQueryInterface;

    /**
     * @return array Get select fields types
     */
    public function getTypes(): array;

    /**
     * @return array Get select fields types which can be nullable
     */
    public function getTypesNullable(): array;
}
