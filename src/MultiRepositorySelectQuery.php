<?php

namespace Squirrel\Entities;

use Squirrel\Queries\DBSelectQueryInterface;

class MultiRepositorySelectQuery implements MultiRepositorySelectQueryInterface
{
    /**
     * @var DBSelectQueryInterface
     */
    private $selectQuery;

    /**
     * @var array
     */
    private $types;

    /**
     * @var array
     */
    private $typesNullable;

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
