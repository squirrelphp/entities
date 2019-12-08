<?php

namespace Squirrel\Entities\Generate;

/**
 * Use PHP tokens to find out which classes are decorated with our entity annotation
 */
class FindClassesWithAnnotation
{
    public function __invoke(string $fileContents): array
    {
        // List of classes, each entry has the namespace as first element and the
        // class name as the second element
        $classes = [];

        // Stores the most recent namespace, import class name and classname in loop
        $namespace = '';
        $className = '';
        $importClassName = '';

        // Get all PHP tokens from a file
        $tokens = \token_get_all($fileContents);

        // Keep track of what name we are collecting at a time
        $namespaceStarted = false;
        $classNameStarted = false;
        $useImportStarted = false;
        $annotationUseFound = false;

        // Go through all PHP tokens
        foreach ($tokens as $key => $token) {
            // "use" name started, so collect all parts until we reach the end of the name
            if ($useImportStarted === true) {
                // String and namespace separator are all part of the class name
                if (\in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    $importClassName .= $token[1];
                } elseif ($token[0] === T_WHITESPACE) { // Ignore whitespace, can be before or after the class name
                } else { // Every other token indicates that we have reached the end of the name
                    $namespaceStarted = false;

                    // We have found the SQLMapper annotation - so there can be entities in this file
                    if ($importClassName === 'Squirrel\\Entities\\Annotation'
                        || $importClassName === 'Squirrel\\Entities\\Annotation\\Entity'
                        || $importClassName === 'Squirrel\\Entities\\Annotation\\Field'
                    ) {
                        $annotationUseFound = true;
                    }
                }
            }

            // "namespace" name started, so collect all parts until we reach the end of the name
            if ($namespaceStarted === true) {
                // String and namespace separator are all part of the namespace
                if (\in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    $namespace .= $token[1];
                } elseif ($token[0] === T_WHITESPACE) { // Ignore whitespace, can be before or after the namespace
                } else { // Every other token indicates that we have reached the end of the name
                    $namespaceStarted = false;
                }
            }

            // "class" name started, so collect all parts until we reach the end of the name
            if ($classNameStarted === true) {
                // String and namespace separator are all part of the class name
                if (\in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    $className .= $token[1];
                } elseif ($token[0] === T_WHITESPACE) { // Ignore whitespace, can be before or after the class name
                } else { // Every other token indicates that we have reached the end of the name
                    $classNameStarted = false;

                    // SQLMapper annotation found beforehand and we have a classname - add it to list
                    if (\strlen($className) > 0 && $annotationUseFound === true) {
                        $classes[] = [$namespace, $className];
                    }

                    // Reset class name to maybe find another one
                    $className = '';
                }
            }

            // "use" token - everything coming after this has to be checked for the
            // SQLMapper annotation class
            if ($token[0] === T_USE) {
                $useImportStarted = true;
                $importClassName = '';
            }

            // "namespace" token - start collecting the namespace name
            if ($token[0] === T_NAMESPACE) {
                $namespaceStarted = true;
                $namespace = '';
            }

            // "class" token - start collecting the class name which is being defined
            if ($token[0] === T_CLASS) {
                $classNameStarted = true;
                $className = '';
            }
        }

        // Return list of the classes found
        return $classes;
    }
}
