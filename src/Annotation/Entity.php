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
     * @var string Name of the SQL table
     */
    public string $name = '';

    /**
     * @var string Database connection - if empty the default connection is used
     */
    public string $connection = '';
}
