<?php

namespace Squirrel\Entities\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Entity
{
    /**
     * @var string Name of the SQL table
     */
    private string $name = '';

    /**
     * @var string Database connection - if empty the default connection is used
     */
    private string $connection = '';

    /**
     * @param string|array{value?: string, name?: string, connection?: string} $name
     */
    public function __construct($name, string $connection = '')
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
            if (isset($name['connection'])) {
                $this->connection = $name['connection'];
            }
        } else {
            $this->name = $name;
            $this->connection = $connection;
        }

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
