<?php

namespace Squirrel\Entities\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Entity
{
    /**
     * Name of the SQL table
     *
     * @var string
     */
    public $name = '';

    /**
     * Database connection - if empty the default connection is used
     *
     * @var string
     */
    public $connection = '';
}
