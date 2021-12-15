<?php

namespace Squirrel\Entities\Generate;

/**
 * Use PHP tokens to find out which classes are decorated by our entity attribute
 */
class FindClassesWithAttribute
{
    public function __invoke(string $fileContents): array
    {
        // List of classes, each entry has the namespace as first element and the
        // class name as the second element
        $classes = [];

        // Stores the most recent namespace, import class name and classname in loop
        $namespace = '';

        // Get all PHP tokens from a file
        $tokens = \token_get_all($fileContents);

        // Keep track of what name we are collecting at a time
        $namespaceStarted = false;
        $classNameStarted = false;
        $useImportStarted = false;
        $attributeStarted = false;
        $attributeUseFound = false;

        // Go through all PHP tokens in the file
        foreach ($tokens as $key => $token) {
            // Skip all whitespace tokens
            if ($token[0] === T_WHITESPACE) {
                continue;
            }

            // Look for usage of our attributes in use imports
            if ($useImportStarted === true) {
                if ($token[0] === T_NAME_QUALIFIED) {
                    if ($this->isAttributeUsage($token[1])) {
                        $attributeUseFound = true;
                    }
                }

                $useImportStarted = false;
            }

            // Look for usage of our attributes in attributes (usually those would be fully qualified, unusual but possible)
            if ($attributeStarted === true) {
                if ($token[0] === T_NAME_FULLY_QUALIFIED || $token[0] === T_NAME_QUALIFIED) {
                    if ($this->isAttributeUsage($token[1])) {
                        $attributeUseFound = true;
                    }
                }

                $attributeStarted = false;
            }

            // Record a new namespace to correctly assign the namespace for found classes
            if ($namespaceStarted === true) {
                if ($token[0] === T_NAME_QUALIFIED || $token[0] === T_STRING) {
                    $namespace = $token[1];
                }

                $namespaceStarted = false;
            }

            // Record any classes if we have found attributes
            if ($classNameStarted === true) {
                if ($token[0] === T_STRING) {
                    if (\strlen($token[1]) > 0 && $attributeUseFound === true) {
                        $classes[] = [$namespace, $token[1]];
                    }
                }

                $classNameStarted = false;
            }

            // "use" token - everything coming after this has to be checked for the attribute classes
            if ($token[0] === T_USE) {
                $useImportStarted = true;
            }

            // "namespace" token - start collecting the namespace name
            if ($token[0] === T_NAMESPACE) {
                $namespaceStarted = true;
                $namespace = '';
            }

            // "class" token - collect the class name if attributes were found earlier
            if ($token[0] === T_CLASS) {
                $classNameStarted = true;
            }

            // "attribute" token - look for fully qualified attribute
            if ($token[0] === T_ATTRIBUTE) {
                $attributeStarted = true;
            }
        }

        // Return list of the classes found
        return $classes;
    }

    private function isAttributeUsage(string $class): bool
    {
        // Remove any preceding slashes
        $class = \ltrim($class, '\\');

        if (\str_starts_with($class, 'Squirrel\\Entities\\Attribute')) {
            return true;
        }

        return false;
    }
}
