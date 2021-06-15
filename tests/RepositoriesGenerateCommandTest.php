<?php

namespace Squirrel\Entities\Tests;

use Squirrel\Entities\Builder\CountEntries;
use Squirrel\Entities\Builder\DeleteEntries;
use Squirrel\Entities\Builder\InsertEntry;
use Squirrel\Entities\Builder\InsertOrUpdateEntry;
use Squirrel\Entities\Builder\UpdateEntries;
use Squirrel\Entities\Generate\PHPFilesInDirectoryGetContents;
use Squirrel\Entities\Generate\RepositoriesGenerateCommand;
use Squirrel\Entities\RepositoryReadOnlyInterface;
use Squirrel\Entities\RepositoryWriteableInterface;
use Symfony\Component\Finder\Finder;

class RepositoriesGenerateCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerationAndValidRepositories(): void
    {
        $validFiles = [
            'NonRepository.php',
            'NonRepositoryWithAttributeInUse.php',
            'User.php',
            'UserAddress.php',
        ];

        $sourceFinder = new Finder();
        $sourceFinder->in(__DIR__ . '/' . 'TestEntities')->files()->sortByName()->ignoreDotFiles(false);

        $foundFiles = [];

        foreach ($sourceFinder as $file) {
            $foundFiles[] = $file->getFilename();
        }

        // Make sure we have the same array contents / the same files
        $this->assertEqualsCanonicalizing($validFiles, $foundFiles);

        $repositoriesGenerator = new RepositoriesGenerateCommand(
            [__DIR__ . '/' . 'TestEntities'],
            new PHPFilesInDirectoryGetContents(),
        );

        // Execute the generator
        $repositoriesGenerator();

        $validFiles = [
            '.gitignore',
            'NonRepository.php',
            'NonRepositoryWithAttributeInUse.php',
            'User.php',
            'UserRepositoryReadOnly.php',
            'UserRepositoryWriteable.php',
            'UserAddress.php',
            'UserAddressRepositoryReadOnly.php',
            'UserAddressRepositoryWriteable.php',
        ];

        $sourceFinder = new Finder();
        $sourceFinder->in(__DIR__ . '/' . 'TestEntities')->files()->sortByName()->ignoreDotFiles(false);

        $foundFiles = [];

        foreach ($sourceFinder as $file) {
            $foundFiles[] = $file->getFilename();
        }

        // Make sure we have the same array contents / the same files
        $this->assertEqualsCanonicalizing($validFiles, $foundFiles);

        $gitignoreContents = '.gitignore' . "\n" .
            'UserRepositoryReadOnly.php' . "\n" . 'UserRepositoryWriteable.php' . "\n" .
            'UserAddressRepositoryReadOnly.php' . "\n" . 'UserAddressRepositoryWriteable.php';

        // Check the .gitignore file contents, that we exclude the right files in the right order
        $this->assertEquals(
            $gitignoreContents,
            \file_get_contents(__DIR__ . '/' . 'TestEntities/' . '.gitignore'),
        );

        // Run repositories generator again to check that the output is identical
        $repositoriesGenerator();

        $sourceFinder = new Finder();
        $sourceFinder->in(__DIR__ . '/' . 'TestEntities')->files()->sortByName()->ignoreDotFiles(false);

        $foundFiles = [];

        foreach ($sourceFinder as $file) {
            $foundFiles[] = $file->getFilename();
        }

        // Make sure we have the same array contents / the same files
        $this->assertEqualsCanonicalizing($validFiles, $foundFiles);

        // Check the .gitignore file contents, that we exclude the right files in the right order
        $this->assertEquals(
            $gitignoreContents,
            \file_get_contents(__DIR__ . '/' . 'TestEntities/' . '.gitignore'),
        );

        // Include our generated classes, making sure that they are valid
        require(__DIR__ . '/' . 'TestEntities/' . 'UserRepositoryReadOnly.php');
        require(__DIR__ . '/' . 'TestEntities/' . 'UserRepositoryWriteable.php');
        require(__DIR__ . '/' . 'TestEntities/' . 'UserAddressRepositoryReadOnly.php');
        require(__DIR__ . '/' . 'TestEntities/' . 'UserAddressRepositoryWriteable.php');

        // Make sure all repository classes exist
        if (!\class_exists('Squirrel\Entities\Tests\TestEntities\UserRepositoryReadOnly', false)) {
            $this->assertEquals('', 'Squirrel\Entities\Tests\TestEntities\UserRepositoryReadOnly');
        }
        if (!\class_exists('Squirrel\Entities\Tests\TestEntities\UserRepositoryWriteable', false)) {
            $this->assertEquals('', 'Squirrel\Entities\Tests\TestEntities\UserRepositoryWriteable');
        }
        if (!\class_exists('Squirrel\Entities\Tests\TestEntities\UserAddressRepositoryReadOnly', false)) {
            $this->assertEquals('', 'Squirrel\Entities\Tests\TestEntities\UserAddressRepositoryReadOnly');
        }
        if (!\class_exists('Squirrel\Entities\Tests\TestEntities\UserAddressRepositoryWriteable', false)) {
            $this->assertEquals('', 'Squirrel\Entities\Tests\TestEntities\UserAddressRepositoryWriteable');
        }
        if (!\class_exists('Squirrel\Entities\Builder\SquirrelEntitiesTestsTestEntitiesUser\SelectEntries', false)) {
            $this->assertEquals(
                '',
                'Squirrel\Entities\Builder\SquirrelEntitiesTestsTestEntitiesUser\SelectEntries',
            );
        }
        if (
            !\class_exists(
                'Squirrel\Entities\Builder\SquirrelEntitiesTestsTestEntitiesUser\SelectIterator',
                false,
            )
        ) {
            $this->assertEquals(
                '',
                'Squirrel\Entities\Builder\SquirrelEntitiesTestsTestEntitiesUser\SelectIterator',
            );
        }
        if (
            !\class_exists(
                'Squirrel\Entities\Builder\SquirrelEntitiesTestsTestEntitiesUserAddress\SelectEntries',
                false,
            )
        ) {
            $this->assertEquals(
                '',
                'Squirrel\Entities\Builder\SquirrelEntitiesTestsTestEntitiesUserAddress\SelectEntries',
            );
        }
        if (
            !\class_exists(
                'Squirrel\Entities\Builder\SquirrelEntitiesTestsTestEntitiesUserAddress\SelectIterator',
                false,
            )
        ) {
            $this->assertEquals(
                '',
                'Squirrel\Entities\Builder\SquirrelEntitiesTestsTestEntitiesUserAddress\SelectIterator',
            );
        }

        $repositoryReadOnly = \Mockery::mock(RepositoryReadOnlyInterface::class);

        $customRepository = new \Squirrel\Entities\Tests\TestEntities\UserRepositoryReadOnly($repositoryReadOnly);

        $this->assertEquals(new CountEntries($repositoryReadOnly), $customRepository->count());
        $this->assertEquals(
            new \Squirrel\Entities\Builder\SquirrelEntitiesTestsTestEntitiesUser\SelectEntries($repositoryReadOnly),
            $customRepository->select(),
        );

        $repositoryWriteable = \Mockery::mock(RepositoryWriteableInterface::class);

        $writeableRepository = new \Squirrel\Entities\Tests\TestEntities\UserRepositoryWriteable($repositoryWriteable);

        $this->assertEquals(new CountEntries($repositoryWriteable), $writeableRepository->count());
        $this->assertEquals(new InsertEntry($repositoryWriteable), $writeableRepository->insert());
        $this->assertEquals(new InsertOrUpdateEntry($repositoryWriteable), $writeableRepository->insertOrUpdate());
        $this->assertEquals(new UpdateEntries($repositoryWriteable), $writeableRepository->update());
        $this->assertEquals(new DeleteEntries($repositoryWriteable), $writeableRepository->delete());

        @\unlink(__DIR__ . '/' . 'TestEntities/' . '.gitignore');
        @\unlink(__DIR__ . '/' . 'TestEntities/' . 'UserRepositoryReadOnly.php');
        @\unlink(__DIR__ . '/' . 'TestEntities/' . 'UserRepositoryWriteable.php');
        @\unlink(__DIR__ . '/' . 'TestEntities/' . 'UserAddressRepositoryReadOnly.php');
        @\unlink(__DIR__ . '/' . 'TestEntities/' . 'UserAddressRepositoryWriteable.php');
    }

    public function testGenerationForInvalidBlobRepositories(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $validFiles = [
            'UserAddressInvalid.php',
        ];

        $sourceFinder = new Finder();
        $sourceFinder->in(__DIR__ . '/' . 'TestEntitiesInvalidBlob')->files()->sortByName()->ignoreDotFiles(false);

        $foundFiles = [];

        foreach ($sourceFinder as $file) {
            $foundFiles[] = $file->getFilename();
        }

        // Make sure we have the same array contents / the same files
        $this->assertEqualsCanonicalizing($validFiles, $foundFiles);

        $repositoriesGenerator = new RepositoriesGenerateCommand(
            [__DIR__ . '/' . 'TestEntitiesInvalidBlob'],
            new PHPFilesInDirectoryGetContents(),
        );

        // Execute the generator
        $repositoriesGenerator();
    }

    public function testGenerationForNoTypeRepositories(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $validFiles = [
            'UserAddressInvalid.php',
        ];

        $sourceFinder = new Finder();
        $sourceFinder->in(__DIR__ . '/' . 'TestEntitiesNoType')->files()->sortByName()->ignoreDotFiles(false);

        $foundFiles = [];

        foreach ($sourceFinder as $file) {
            $foundFiles[] = $file->getFilename();
        }

        // Make sure we have the same array contents / the same files
        $this->assertEqualsCanonicalizing($validFiles, $foundFiles);

        $repositoriesGenerator = new RepositoriesGenerateCommand(
            [__DIR__ . '/' . 'TestEntitiesNoType'],
            new PHPFilesInDirectoryGetContents(),
        );

        // Execute the generator
        $repositoriesGenerator();
    }

    public function testGenerationForInvalidNoEntityNameRepositories(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $validFiles = [
            'UserAddressInvalid.php',
        ];

        $sourceFinder = new Finder();
        $sourceFinder->in(__DIR__ . '/' . 'TestEntitiesInvalidNoEntityName')->files()->sortByName()->ignoreDotFiles(false);

        $foundFiles = [];

        foreach ($sourceFinder as $file) {
            $foundFiles[] = $file->getFilename();
        }

        // Make sure we have the same array contents / the same files
        $this->assertEqualsCanonicalizing($validFiles, $foundFiles);

        $repositoriesGenerator = new RepositoriesGenerateCommand(
            [__DIR__ . '/' . 'TestEntitiesInvalidNoEntityName'],
            new PHPFilesInDirectoryGetContents(),
        );

        // Execute the generator
        $repositoriesGenerator();
    }

    public function testGenerationForInvalidNoFieldNameRepositories(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $validFiles = [
            'UserAddressInvalid.php',
        ];

        $sourceFinder = new Finder();
        $sourceFinder->in(__DIR__ . '/' . 'TestEntitiesInvalidNoFieldName')->files()->sortByName()->ignoreDotFiles(false);

        $foundFiles = [];

        foreach ($sourceFinder as $file) {
            $foundFiles[] = $file->getFilename();
        }

        // Make sure we have the same array contents / the same files
        $this->assertEqualsCanonicalizing($validFiles, $foundFiles);

        $repositoriesGenerator = new RepositoriesGenerateCommand(
            [__DIR__ . '/' . 'TestEntitiesInvalidNoFieldName'],
            new PHPFilesInDirectoryGetContents(),
        );

        // Execute the generator
        $repositoriesGenerator();
    }

    public function testGenerationForInvalidFieldUnionTypeRepositories(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $validFiles = [
            'UserAddressInvalid.php',
        ];

        $sourceFinder = new Finder();
        $sourceFinder->in(__DIR__ . '/' . 'TestEntitiesInvalidFieldUnionType')->files()->sortByName()->ignoreDotFiles(false);

        $foundFiles = [];

        foreach ($sourceFinder as $file) {
            $foundFiles[] = $file->getFilename();
        }

        // Make sure we have the same array contents / the same files
        $this->assertEqualsCanonicalizing($validFiles, $foundFiles);

        $repositoriesGenerator = new RepositoriesGenerateCommand(
            [__DIR__ . '/' . 'TestEntitiesInvalidFieldUnionType'],
            new PHPFilesInDirectoryGetContents(),
        );

        // Execute the generator
        $repositoriesGenerator();
    }
}
