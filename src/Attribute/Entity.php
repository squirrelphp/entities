<?php

namespace Squirrel\Entities\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Entity
{
    public function __construct(
        /** @var string Name of the SQL table */
        private string $name,
        /** @var string Database connection - if empty the default connection is used */
        private string $connection = '',
    ) {
        if (\strlen($this->name) === 0) {
            throw new \InvalidArgumentException('No name provided for entity');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConnection(): string
    {
        return $this->connection;
    }
}
