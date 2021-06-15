<?php

namespace Squirrel\Entities\Attribute;

use Squirrel\Entities\RepositoryConfig;
use Squirrel\Entities\RepositoryConfigInterface;

/**
 * Processes entity classes
 */
class EntityProcessor
{
    /**
     * Processes a class according to its attributes
     *
     * @psalm-param object|class-string $class
     */
    public function process(object|string $class): ?RepositoryConfigInterface
    {
        // Get class reflection data
        $reflectionClass = new \ReflectionClass($class);

        $entity = $this->getEntityFromAttribute($reflectionClass);

        // This class was annotated as Entity
        if ($entity instanceof Entity) {
            // Configuration options which need to be populated
            $tableToObjectFields = [];
            $objectToTableFields = [];
            $objectTypes = [];
            $objectTypesNullable = [];
            $autoincrement = '';

            // Go through all public values of the class
            foreach ($reflectionClass->getProperties() as $property) {
                $field = $this->getFieldFromAttribute($property);

                // A Field attribute was found
                if ($field instanceof Field) {
                    $fieldType = $property->getType();

                    // We need property types to know what to cast fields to
                    if ($fieldType === null) {
                        throw new \InvalidArgumentException('No property type for property field ' . $property->getName() . ' in ' . $reflectionClass->getName());
                    }

                    if ($fieldType instanceof \ReflectionUnionType) {
                        throw new \InvalidArgumentException('Union property types are not supported, encountered with property field ' . $property->getName() . ' in ' . $reflectionClass->getName());
                    }

                    if (!$fieldType instanceof \ReflectionNamedType) {
                        throw new \InvalidArgumentException('Property type is not a named type, encountered with property field ' . $property->getName() . ' in ' . $reflectionClass->getName());
                    }

                    $fieldTypeName = $fieldType->getName();

                    if ($field->isBlob() === true && $fieldTypeName === 'string') {
                        $fieldTypeName = 'blob';
                    } elseif ($field->isBlob() === true) {
                        throw new \InvalidArgumentException('Blob property type set for a non-string property field: ' . $property->getName() . ' in ' . $reflectionClass->getName());
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
                $reflectionClass->getName(),
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
            return null;
        }

        return $attributes[0]->newInstance();
    }

    private function getFieldFromAttribute(\ReflectionProperty $property): ?Field
    {
        $attributes = $property->getAttributes(Field::class);

        if (\count($attributes) === 0) {
            return null;
        }

        return $attributes[0]->newInstance();
    }
}
