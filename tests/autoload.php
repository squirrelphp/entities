<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

foreach ([
     __DIR__ . '/../../autoload.php',
     __DIR__ . '/../../../autoload.php',
     __DIR__ . '/../vendor/autoload.php',
 ] as $file) {
    if (file_exists($file)) {
        /**
         * @var ClassLoader $loader
         */
        $loader = require $file;

        break;
    }
}

// Needed so annotation classes are loaded, but will be removed in doctrine/annotations 2.0,
// so we might need to change this to make it work for that version when it is released
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
