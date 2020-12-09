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
                // @codeCoverageIgnoreStart
                // In PHP8 the whole class namespace is its own token
                if (PHP_VERSION_ID >= 80000 && $token[0] === T_NAME_QUALIFIED) {
                    $importClassName = $token[1];
                } elseif (PHP_VERSION_ID < 80000 && \in_array($token[0], [T_STRING, T_NS_SEPARATOR], true)) {
                    // In PHP 7.4 the namespace is made of string and namespace separators
                    $importClassName .= $token[1];
                } elseif ($token[0] === T_WHITESPACE) { // Ignore whitespace, can be before or after the class name
                } else { // Every other token indicates that we have reached the end of the name
                    $namespaceStarted = false;

                    // We have found the annotation namespace - so there can be entities in this file
                    if (
                        $importClassName === 'Squirrel\\Entities\\Annotation'
                        || $importClassName === 'Squirrel\\Entities\\Annotation\\Entity'
                        || $importClassName === 'Squirrel\\Entities\\Annotation\\Field'
                    ) {
                        $annotationUseFound = true;
                    }
                }
                // @codeCoverageIgnoreEnd
            }

            // "namespace" name started, so collect all parts until we reach the end of the name
            if ($namespaceStarted === true) {
                // @codeCoverageIgnoreStart
                // In PHP8 the whole class namespace can be its own token
                if (PHP_VERSION_ID >= 80000 && $token[0] === T_NAME_QUALIFIED) {
                    $namespace = $token[1];
                    $namespaceStarted = false;
                } elseif (\in_array($token[0], [T_STRING, T_NS_SEPARATOR], true)) {
                    // In PHP 7.4 the namespace is made of string and namespace separators
                    // In PHP8 the namespace can be one string
                    $namespace .= $token[1];
                } elseif ($token[0] === T_WHITESPACE) { // Ignore whitespace, can be before or after the namespace
                } else { // Every other token indicates that we have reached the end of the name
                    $namespaceStarted = false;
                }
                // @codeCoverageIgnoreEnd
            }

            // "class" name started, so collect all parts until we reach the end of the name
            if ($classNameStarted === true) {
                // Only a string is expected for the class name
                if ($token[0] === T_STRING) {
                    if (\strlen($token[1]) > 0 && $annotationUseFound === true) {
                        $classes[] = [$namespace, $token[1]];
                    }
                } elseif ($token[0] === T_WHITESPACE) { // Ignore whitespace, can be before or after the class name
                } else { // Every other token indicates that we have reached the end of the name
                    $classNameStarted = false;
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
            }
        }

        // Return list of the classes found
        return $classes;
    }
}
