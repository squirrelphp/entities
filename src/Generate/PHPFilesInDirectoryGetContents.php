<?php

namespace Squirrel\Entities\Generate;

use Symfony\Component\Finder\Finder;

class PHPFilesInDirectoryGetContents
{
    public function __invoke(string $directory): iterable
    {
        $sourceFinder = new Finder();
        $sourceFinder->in($directory)->files()->name('*.php')->sortByName();

        // Go through files which were found
        foreach ($sourceFinder as $file) {
            // Safety check because Finder can return false if the file was not found
            if ($file->getRealPath() === false) {
                throw new \InvalidArgumentException('File in source directory not found');
            }

            // Get file contents
            $fileContents = \file_get_contents($file->getRealPath());

            // Another safety check because file_get_contents can return false if the file was not found
            if ($fileContents === false) {
                throw new \InvalidArgumentException('File in source directory could not be retrieved');
            }

            yield [
                'filename' => $file->getFilename(),
                'path' => $file->getPath(),
                'contents' => $fileContents,
            ];
        }
    }
}
