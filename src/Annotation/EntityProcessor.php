<?php

namespace Squirrel\Entities\Annotation;

use Doctrine\Common\Annotations\Reader;
use Squirrel\Entities\RepositoryConfig;

/**
 * Processes entity classes
 */
class EntityProcessor
{
    /**
     * Annotation reader
     *
     * @var Reader
     */
    private $annotationReader;

    /**
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Processes a class according to its Convert annotation
     *
     * @param object|string $class
     * @return null|RepositoryConfig
     */
    public function process($class)
    {
        // Get class reflection data
        $annotationClass = new \ReflectionClass($class);

        // Get entity annotation for class
        $entity = $this->annotationReader->getClassAnnotation($annotationClass, Entity::class);

        // This class was annotated as Entity
        if ($entity instanceof Entity) {
            // Configuration options which need to be populated
            $tableToObjectFields = [];
            $objectToTableFields = [];
            $objectTypes = [];
            $objectTypesNullable = [];
            $autoincrement = '';

            // Go through all public values of the class
            foreach ($annotationClass->getProperties() as $property) {
                // Get annotations for a propery
                $annotationProperty = new \ReflectionProperty($class, $property->getName());

                // Find Field annotation on the property
                $field = $this->annotationReader->getPropertyAnnotation(
                    $annotationProperty,
                    Field::class
                );

                // A Field annotation was found
                if ($field instanceof Field) {
                    $tableToObjectFields[$field->name] = $property->getName();
                    $objectToTableFields[$property->getName()] = $field->name;
                    $objectTypes[$property->getName()] = $field->type;
                    $objectTypesNullable[$property->getName()] = $field->nullable;

                    if ($field->autoincrement === true) {
                        $autoincrement = $field->name;
                    }
                }
            }

            // Create new config for a repository
            return new RepositoryConfig(
                $entity->connection,
                $entity->name,
                $tableToObjectFields,
                $objectToTableFields,
                $annotationClass->getName(),
                $objectTypes,
                $objectTypesNullable,
                $autoincrement
            );
        }

        // No entity found, so no configuration could be generated
        return null;
    }
}
