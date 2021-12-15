<?php

namespace Squirrel\Entities\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Field
{
    public function __construct(
        /** @var string Name of the field in the SQL table */
        private string $name,
        /** @var bool Whether this is the autoincrement field for the table - only one per table is legal! */
        private bool $autoincrement = false,
        /** @var bool Whether this is a blob field (binary large object) - needed for Postgres compatibility */
        private bool $blob = false,
    ) {
        if (\strlen($this->name) === 0) {
            throw new \InvalidArgumentException('No name provided for field');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isAutoincrement(): bool
    {
        return $this->autoincrement;
    }

    public function isBlob(): bool
    {
        return $this->blob;
    }
}
