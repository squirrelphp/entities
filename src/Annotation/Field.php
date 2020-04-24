<?php

namespace Squirrel\Entities\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Field
{
    /**
     * @var string Name of the field in the SQL table
     */
    public string $name = '';

    /**
     * @var bool Whether this is the autoincrement field for the table - only one per table is legal!
     */
    public bool $autoincrement = false;

    /**
     * @var bool Whether this is a blob field (binary large object) - needed for Postgres compatibility
     */
    public bool $blob = false;
}
