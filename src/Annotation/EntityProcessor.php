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
     */
    public function process($class): ?RepositoryConfig
    {
        // Get class reflection data
        $annotationClass = new \ReflectionClass($class);

        // @codeCoverageIgnoreStart
        if (PHP_VERSION_ID >= 80000) {
            $entity = $this->getEntityFromAttribute($annotationClass);
        } else {
            $entity = $this->getEntityFromAnnotation($annotationClass);
        }
        // @codeCoverageIgnoreEnd

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
                // @codeCoverageIgnoreStart
                if (PHP_VERSION_ID >= 80000) {
                    $field = $this->getFieldFromAttribute($property);
                } else {
                    $field = $this->getFieldFromAnnotation($property);
                }
                // @codeCoverageIgnoreEnd

                // A Field annotation was found
                if ($field instanceof Field) {
                    $fieldType = $property->getType();

                    // We need property types to know what to cast fields to
                    if ($fieldType === null) {
                        throw new \InvalidArgumentException('No property type for property field ' . $property->getName() . ' in ' . $annotationClass->getName());
                    }

                    if ($fieldType instanceof \ReflectionUnionType) {
                        throw new \InvalidArgumentException('Union property types are not supported, encountered with property field ' . $property->getName() . ' in ' . $annotationClass->getName());
                    }

                    $fieldTypeName = $fieldType->getName();

                    if ($field->isBlob() === true && $fieldTypeName === 'string') {
                        $fieldTypeName = 'blob';
                    } elseif ($field->isBlob() === true) {
                        throw new \InvalidArgumentException('Blob property type set for a non-string property field: ' . $property->getName() . ' in ' . $annotationClass->getName());
                    }

                    $tableToObjectFields[$field->getName()] = $property->getName();
                    $objectToTableFields[$property->getName()] = $field->getName();
                    $objectTypes[$property->getName()] = $fieldTypeName;
                    $objectTypesNullable[$property->getName()] = $fieldType->allowsNull();

                    if ($field->isAutoincrement() === true) {
                        $autoincrement = $field->getName();
                    }
                }
            }

            // Create new config for a repository
            return new RepositoryConfig(
                $entity->getConnection(),
                $entity->getName(),
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

    private function getEntityFromAttribute(\ReflectionClass $class): ?Entity
    {
        $attributes = $class->getAttributes(Entity::class);

        if (\count($attributes) === 0) {
            return $this->getEntityFromAnnotation($class);
        }

        return $attributes[0]->newInstance();
    }

    private function getEntityFromAnnotation(\ReflectionClass $class): ?Entity
    {
        $entity = $this->annotationReader->getClassAnnotation($class, Entity::class);

        // A StringFilters annotation was not found
        if (!($entity instanceof Entity)) {
            return null;
        }

        return $entity;
    }

    private function getFieldFromAttribute(\ReflectionProperty $property): ?Field
    {
        $attributes = $property->getAttributes(Field::class);

        if (\count($attributes) === 0) {
            return $this->getFieldFromAnnotation($property);
        }

        return $attributes[0]->newInstance();
    }

    private function getFieldFromAnnotation(\ReflectionProperty $property): ?Field
    {
        // Find StringFilter annotation on the property
        $stringFilters = $this->annotationReader->getPropertyAnnotation(
            $property,
            Field::class,
        );

        // A StringFilters annotation was not found
        if (!($stringFilters instanceof Field)) {
            return null;
        }

        return $stringFilters;
    }
}
