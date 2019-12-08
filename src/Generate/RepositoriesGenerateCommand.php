<?php

namespace Squirrel\Entities\Generate;

use Doctrine\Common\Annotations\AnnotationReader;
use Squirrel\Entities\Annotation\EntityProcessor;

/**
 * Generate repositories and service definitions for SQLMapper entities
 */
class RepositoriesGenerateCommand
{
    /**
     * @var FindClassesWithAnnotation
     */
    private $findClassesWithAnnotation;

    /**
     * @var string[]
     */
    private $sourceCodeDirectories;

    /**
     * @var array
     */
    private $repositoryPhpFileBlueprint = [
        'ReadOnly' => <<<'EOD'
<?php
// phpcs:ignoreFile -- created by SquirrelPHP library, do not alter
/*
 * THIS FILE IS AUTOMATICALLY CREATED - DO NOT EDIT, DO NOT COMMIT TO VCS
 *
 * IF YOU DELETE THE ENTITY ({namespaceOfEntity}\{classOfEntity})
 * THEN PLEASE DELETE THIS FILE - IT WILL NO LONGER BE NEEDED
 *
 * Generated by Squirrel\Entities\Generate\RepositoriesGenerateCommand,
 * this file will be overwritten when that command is executed again, if your
 * entity still exists at that time
 */
// @codeCoverageIgnoreStart

namespace {namespaceOfEntity} {
    use Squirrel\Entities\RepositoryBuilderReadOnlyInterface;
    use Squirrel\Entities\RepositoryReadOnlyInterface;

    class {classOfEntity}RepositoryReadOnly implements RepositoryBuilderReadOnlyInterface
    {
        /**
         * @var RepositoryReadOnlyInterface
         */
        private $repository;

        public function __construct(RepositoryReadOnlyInterface $repository)
        {
            $this->repository = $repository;
        }

        public function count(): \Squirrel\Entities\Action\CountEntries
        {
            return new \Squirrel\Entities\Action\CountEntries($this->repository);
        }

        public function select(): \{namespaceOfBuilders}\SelectEntries
        {
            return new \{namespaceOfBuilders}\SelectEntries($this->repository);
        }
    }
}

namespace {namespaceOfBuilders} {
    /*
     * This class exists to have proper type hints about the object(s) returned in the
     * getEntries and getOneEntry functions. All calls are delegated to the
     * SelectEntries class - because of the builder pattern we cannot extend SelectEntries
     * (because then returning self would return that class instead of this extended class)
     * so we instead imitate it. This way the implementation in SelectEntries can change
     * and this generated class has no ties to how it "works" or how the repository is used.
     */
    class SelectEntries implements \Squirrel\Entities\Action\ActionInterface, \IteratorAggregate
    {
        /**
         * @var \Squirrel\Entities\Action\SelectEntries
         */
        private $selectImplementation;

        public function __construct(\Squirrel\Entities\RepositoryReadOnlyInterface $repository)
        {
            $this->selectImplementation = new \Squirrel\Entities\Action\SelectEntries($repository);
        }

        public function field(string $onlyGetThisField): self
        {
            $this->selectImplementation->field($onlyGetThisField);
            return $this;
        }

        public function fields(array $onlyGetTheseFields): self
        {
            $this->selectImplementation->fields($onlyGetTheseFields);
            return $this;
        }

        public function where(array $whereClauses): self
        {
            $this->selectImplementation->where($whereClauses);
            return $this;
        }

        /**
         * @param array|string $orderByClauses
         * @return SelectEntries
         */
        public function orderBy($orderByClauses): self
        {
            $this->selectImplementation->orderBy($orderByClauses);
            return $this;
        }

        public function startAt(int $startAtNumber): self
        {
            $this->selectImplementation->startAt($startAtNumber);
            return $this;
        }

        public function limitTo(int $numberOfEntries): self
        {
            $this->selectImplementation->limitTo($numberOfEntries);
            return $this;
        }

        public function blocking(bool $active = true): self
        {
            $this->selectImplementation->blocking($active);
            return $this;
        }

        /**
         * @return \{namespaceOfEntity}\{classOfEntity}[]
         */
        public function getAllEntries(): array
        {
            return $this->selectImplementation->getAllEntries();
        }

        public function getOneEntry(): ?\{namespaceOfEntity}\{classOfEntity}
        {
            return $this->selectImplementation->getOneEntry();
        }

        /**
         * @return string[]|int[]|float[]|bool[]|null[]
         */
        public function getFlattenedFields(): array
        {
            return $this->selectImplementation->getFlattenedFields();
        }

        public function getIterator(): SelectIterator
        {
            return new SelectIterator($this->selectImplementation->getIterator());
        }
    }

    class SelectIterator implements \Squirrel\Entities\Action\ActionInterface, \Iterator
    {
        /**
         * @var \Squirrel\Entities\Action\SelectIterator
         */
        private $iteratorInstance;

        public function __construct(\Squirrel\Entities\Action\SelectIterator $iterator)
        {
            $this->iteratorInstance = $iterator;
        }

        /**
         * @return \{namespaceOfEntity}\{classOfEntity}
         */
        public function current()
        {
            return $this->iteratorInstance->current();
        }

        public function next(): void
        {
            $this->iteratorInstance->next();
        }

        /**
         * @return int
         */
        public function key()
        {
            return $this->iteratorInstance->key();
        }

        /**
         * @return bool
         */
        public function valid()
        {
            return $this->iteratorInstance->valid();
        }

        public function rewind(): void
        {
            $this->iteratorInstance->rewind();
        }

        public function clear(): void
        {
            $this->iteratorInstance->clear();
        }
    }
}
// @codeCoverageIgnoreEnd

EOD
        ,
        'Writeable' => <<<'EOD'
<?php
// phpcs:ignoreFile -- created by SquirrelPHP library, do not alter
/*
 * THIS FILE IS AUTOMATICALLY CREATED - DO NOT EDIT, DO NOT COMMIT TO VCS
 *
 * IF YOU DELETE THE ENTITY ({namespaceOfEntity}\{classOfEntity})
 * THEN PLEASE DELETE THIS FILE - IT WILL NO LONGER BE NEEDED
 *
 * Generated by Squirrel\Entities\Generate\RepositoriesGenerateCommand,
 * this file will be overwritten when that command is executed again, if your
 * entity still exists at that time
 */
// @codeCoverageIgnoreStart

namespace {namespaceOfEntity} {
    use Squirrel\Entities\RepositoryBuilderWriteableInterface;
    use Squirrel\Entities\RepositoryWriteableInterface;

    class {classOfEntity}RepositoryWriteable extends {classOfEntity}RepositoryReadOnly implements
        RepositoryBuilderWriteableInterface
    {
        /**
         * @var RepositoryWriteableInterface
         */
        private $repository;

        public function __construct(RepositoryWriteableInterface $repository)
        {
            $this->repository = $repository;
            parent::__construct($repository);
        }

        public function insert(): \Squirrel\Entities\Action\InsertEntry
        {
            return new \Squirrel\Entities\Action\InsertEntry($this->repository);
        }

        public function insertOrUpdate(): \Squirrel\Entities\Action\InsertOrUpdateEntry
        {
            return new \Squirrel\Entities\Action\InsertOrUpdateEntry($this->repository);
        }

        public function update(): \Squirrel\Entities\Action\UpdateEntries
        {
            return new \Squirrel\Entities\Action\UpdateEntries($this->repository);
        }

        public function delete(): \Squirrel\Entities\Action\DeleteEntries
        {
            return new \Squirrel\Entities\Action\DeleteEntries($this->repository);
        }
    }
}
// @codeCoverageIgnoreEnd

EOD
        ,
    ];

    /**
     * @var PHPFilesInDirectoryGetContents
     */
    private $PHPFilesInDirectoryGetContents;

    /**
     * @param string[] $sourceCodeDirectories
     * @param PHPFilesInDirectoryGetContents $PHPFilesInDirectoryGetContents
     */
    public function __construct(
        array $sourceCodeDirectories,
        PHPFilesInDirectoryGetContents $PHPFilesInDirectoryGetContents
    ) {
        $this->findClassesWithAnnotation = new FindClassesWithAnnotation();
        $this->sourceCodeDirectories = $sourceCodeDirectories;
        $this->PHPFilesInDirectoryGetContents = $PHPFilesInDirectoryGetContents;
    }

    /**
     * @return string[]
     */
    public function __invoke(): array
    {
        $log = [];

        // Initialize entity processor to find repository config
        $entityProcessor = new EntityProcessor(new AnnotationReader());

        // Saves the files per path for which to create a .gitignore file
        $gitignoreFilesForPaths = [];

        // Go through directories
        foreach ($this->sourceCodeDirectories as $directory) {
            // Go through files which were found
            foreach (($this->PHPFilesInDirectoryGetContents)($directory) as $fileData) {
                // Get all possible entity classes with our annotation
                $classes = $this->findClassesWithAnnotation->__invoke($fileData['contents']);

                // Go through the possible entity classes
                foreach ($classes as $class) {
                    // Divvy up the namespace and the class name
                    $namespace = $class[0];
                    $className = $class[1];

                    /**
                     * @psalm-var class-string $fullClassName
                     */
                    $fullClassName = $namespace . '\\' . $className;

                    // Get repository config as object from annotations
                    $repositoryConfig = $entityProcessor->process($fullClassName);

                    // Repository config found - this is definitely an entity
                    if (isset($repositoryConfig)) {
                        $log[] = 'Entity found: ' . $fullClassName;

                        $gitignoreFilesForPaths[$fileData['path']][] = $this->generateRepositoryFile(
                            $namespace,
                            $className,
                            $fileData,
                            'ReadOnly'
                        );

                        $gitignoreFilesForPaths[$fileData['path']][] = $this->generateRepositoryFile(
                            $namespace,
                            $className,
                            $fileData,
                            'Writeable'
                        );
                    }
                }
            }
        }

        // Go through all paths where we created repository files
        $this->createGitignoreFiles($gitignoreFilesForPaths);

        return $log;
    }

    private function createGitignoreFiles(array $gitignoreFilesForPaths): void
    {
        // Go through all paths where we created repository files
        foreach ($gitignoreFilesForPaths as $path => $files) {
            // Make sure all files are unique / no duplicates
            $files = \array_unique($files);

            if (\count($files) > 0) {
                // Ignore the .gitignore file in entity directories, that way both the gitignore
                // and the repositories will be ignored by VCS
                $gitignoreContents = [
                    '.gitignore',
                ];

                // Add each repository file to .gitignore
                foreach ($files as $filename) {
                    $gitignoreContents[] = $filename;
                }

                // Save .gitignore file in the appropriate path
                \file_put_contents(
                    $path . '/.gitignore',
                    \implode("\n", $gitignoreContents)
                );
            }
        }
    }

    private function generateRepositoryFile(string $namespace, string $className, array $fileData, string $type): string
    {
        $phpFilename = \str_replace('.php', '', $fileData['filename']) . 'Repository' . $type . '.php';

        // Compile file name and file contents for repository
        $fullPhpFilename = $fileData['path'] . '/' . $phpFilename;
        $fileContents = $this->repositoryFileContentsFillInBlueprint(
            $this->repositoryPhpFileBlueprint[$type],
            $namespace,
            $className
        );

        // Save repository PHP file - only if it changed or doesn't exist yet
        if (!\file_exists($fullPhpFilename) || \file_get_contents($fullPhpFilename) !== $fileContents) {
            \file_put_contents($fullPhpFilename, $fileContents);
        }

        // Add PHP file to list for which we want to create a .gitignore file
        return $phpFilename;
    }

    private function repositoryFileContentsFillInBlueprint(
        string $repositoryPhpFile,
        string $namespace,
        string $className
    ): string {
        $fullClassnameWithoutSeparator = \str_replace(
            '\\',
            '',
            $namespace . $className
        );
        $repositoryPhpFile = \str_replace(
            '{namespaceOfEntity}',
            $namespace,
            $repositoryPhpFile
        );
        $repositoryPhpFile = \str_replace(
            '{namespaceOfBuilders}',
            'Squirrel\\Entities\\Action\\' . $fullClassnameWithoutSeparator,
            $repositoryPhpFile
        );
        $repositoryPhpFile = \str_replace(
            '{classOfEntity}',
            $className,
            $repositoryPhpFile
        );
        return $repositoryPhpFile;
    }
}
