<?php

namespace Squirrel\Entities;

use Squirrel\Debug\Debug;

trait EntityConstructorTrait
{
    /**
     * Initialize the object with an array - not used by repository, as the repository uses reflection to
     * set entity values, but a constructor can be helpful for testing or other special/explicit usages
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (!\property_exists($this, $key)) {
                throw new \InvalidArgumentException(
                    'Property "' . $key . '" does not exist in entity class when attempting to construct with: ' .
                    Debug::sanitizeData($data)
                );
            }

            $this->$key = $value;
        }
    }
}
