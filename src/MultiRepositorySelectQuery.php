<?php

namespace Squirrel\Entities;

use Squirrel\Queries\DBSelectQueryInterface;

class MultiRepositorySelectQuery implements MultiRepositorySelectQueryInterface
{
    private DBSelectQueryInterface $selectQuery;
    private array $types;
    private array $typesNullable;

    public function __construct(DBSelectQueryInterface $selectQuery, array $types, array $typesNullable)
    {
        $this->selectQuery = $selectQuery;
        $this->types = $types;
        $this->typesNullable = $typesNullable;
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
