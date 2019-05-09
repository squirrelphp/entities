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
    public $name = '';

    /**
     * Type of the field in the SQL table - can be one of the following:
     *
     * - string (default)
     * - int
     * - float
     * - bool
     *
     * @var string Type of the field in the SQL table
     */
    public $type = 'string';

    /**
     * @var bool Whether the field can be NULL as a special value in addition to the field type
     */
    public $nullable = false;

    /**
     * @var bool Whether this is the autoincrement field for the table - only one per table is legal!
     */
    public $autoincrement = false;
}
