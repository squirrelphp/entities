<?php

namespace Squirrel\Entities;

use Squirrel\Queries\DBSelectQueryInterface;

class MultiRepositorySelectQuery implements MultiRepositorySelectQueryInterface
{
    public function __construct(
        private DBSelectQueryInterface $selectQuery,
        private array $types,
        private array $typesNullable,
    ) {
    }

    public function getQuery(): DBSelectQueryInterface
    {
        return $this->selectQuery;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function getTypesNullable(): array
    {
        return $this->typesNullable;
    }
}
