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
     */
    private Reader $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Processes a class according to its Convert annotation
     *
     * @param object|string $class
     * @psalm-param object|class-string $class
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
                    Field::class,
                );

                // A Field annotation was found
                if ($field instanceof Field) {
                    $fieldType = $annotationProperty->getType();

                    // We need property types to know what to cast fields to
                    if (!isset($fieldType)) {
                        throw new \InvalidArgumentException('No property type for property field ' . $property->getName() . ' in ' . $annotationClass->getName());
                    }

                    $fieldTypeName = $fieldType->getName();

                    if ($field->blob === true && $fieldTypeName === 'string') {
                        $fieldTypeName = 'blob';
                    } elseif ($field->blob === true) {
                        throw new \InvalidArgumentException('Blob property type set for a non-string property field: ' . $property->getName() . ' in ' . $annotationClass->getName());
                    }

                    $tableToObjectFields[$field->name] = $property->getName();
                    $objectToTableFields[$property->getName()] = $field->name;
                    $objectTypes[$property->getName()] = $fieldTypeName;
                    $objectTypesNullable[$property->getName()] = $fieldType->allowsNull();

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
                $autoincrement,
            );
        }

        // No entity found, so no configuration could be generated
        return null;
    }
}
