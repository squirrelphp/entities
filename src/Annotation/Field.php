<?php

namespace Squirrel\Entities\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Field
{
    /**
     * @var string Name of the field in the SQL table
     */
    private string $name = '';

    /**
     * @var bool Whether this is the autoincrement field for the table - only one per table is legal!
     */
    private bool $autoincrement = false;

    /**
     * @var bool Whether this is a blob field (binary large object) - needed for Postgres compatibility
     */
    private bool $blob = false;

    /**
     * @param string|array{value?: string, name?: string, autoincrement?: bool, blob?: bool} $name
     */
    public function __construct($name, bool $autoincrement = false, bool $blob = false)
    {
        // Doctrine annotations always provides an array as a first argument - this is for backwards compatibility
        if (\is_array($name)) {
            // First value is provided directly for the name
            if (isset($name['value'])) {
                $this->name = $name['value'];
            }

            // All values as "named parameters" from annotations
            if (isset($name['name'])) {
                $this->name = $name['name'];
            }
            if (isset($name['autoincrement'])) {
                $this->autoincrement = $name['autoincrement'];
            }
            if (isset($name['blob'])) {
                $this->blob = $name['blob'];
            }
        } else {
            $this->name = $name;
            $this->autoincrement = $autoincrement;
            $this->blob = $blob;
        }

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
