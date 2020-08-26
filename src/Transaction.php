<?php

namespace Squirrel\Entities;

use Squirrel\Debug\Debug;
use Squirrel\Queries\DBException;
use Squirrel\Queries\DBInterface;
use Squirrel\Queries\Exception\DBInvalidOptionException;

/**
 * Run queries within a transaction
 */
class Transaction implements TransactionInterface
{
    private DBInterface $db;

    public function __construct(DBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Create transaction with given repositories, making sure they all use the same database connection
     *
     * @param array<RepositoryReadOnlyInterface|RepositoryBuilderReadOnlyInterface> $repositories
     * @return self
     *
     * @throws DBException Common minimal exception thrown if anything goes wrong
     */
    public static function withRepositories(array $repositories): self
    {
        /**
         * Connection to use for transaction
         *
         * @var DBInterface|null $connection
         */
        $connection = null;

        // Go through all repositories
        foreach ($repositories as $repository) {
            // Builder repository found - get the base repository from it
            if ($repository instanceof RepositoryBuilderReadOnlyInterface) {
                try {
                    $builderRepositoryReflection = new \ReflectionClass($repository);
                    $builderRepositoryPropertyReflection = $builderRepositoryReflection->getProperty('repository');
                    $builderRepositoryPropertyReflection->setAccessible(true);
                    $repository = $builderRepositoryPropertyReflection->getValue($repository);
                } catch (\ReflectionException $e) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        [Transaction::class],
                        'Base repository not found in builder repository via reflection. ' .
                        'Make sure you use officially supported classes',
                    );
                }
            }

            // Base repository found - get the DBInterface from it
            if ($repository instanceof RepositoryReadOnlyInterface) {
                try {
                    $baseRepositoryReflection = new \ReflectionClass($repository);
                    $baseRepositoryPropertyReflection = $baseRepositoryReflection->getProperty('db');
                    $baseRepositoryPropertyReflection->setAccessible(true);
                    $foundConnection = $baseRepositoryPropertyReflection->getValue($repository);
                } catch (\ReflectionException $e) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        [Transaction::class],
                        'Connection not found in base repository via reflection. ' .
                        'Make sure you use officially supported classes',
                    );
                }

                // Make sure all repositories are using the same connection, otherwise a transaction is impossible
                if (isset($connection) && $connection !== $foundConnection) {
                    throw Debug::createException(
                        DBInvalidOptionException::class,
                        [TransactionInterface::class],
                        'Repositories have different database connections, transaction is not possible',
                    );
                }

                $connection = $foundConnection;
            } else { // No base repository - meaning this class is invalid
                throw Debug::createException(
                    DBInvalidOptionException::class,
                    [TransactionInterface::class],
                    'Invalid class specified to create transaction (not a repository)',
                );
            }
        }

        // No connection found, meaning no repositories were defined in arguments
        if (!isset($connection)) {
            throw Debug::createException(
                DBInvalidOptionException::class,
                [TransactionInterface::class],
                'No repositories for transaction defined',
            );
        }

        return new self($connection);
    }

    public function run(callable $func, ...$arguments)
    {
        return $this->db->transaction($func, ...$arguments);
    }
}
