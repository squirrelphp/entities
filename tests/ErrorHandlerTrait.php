<?php

namespace Squirrel\Entities\Tests;

trait ErrorHandlerTrait
{
    protected mixed $phpunitErrorHandler = null;
    protected array $listOfDeprecations = [];

    protected function allowDeprecations(): void
    {
        $this->listOfDeprecations = [];

        $this->phpunitErrorHandler = \set_error_handler(function (int $severity, string $message, string $filepath, int $line): bool {
            if ($severity === E_USER_DEPRECATED) {
                $this->listOfDeprecations[] = $message;
            } else {
                return ($this->phpunitErrorHandler)($severity, $message, $filepath, $line);
            }

            return true;
        });
    }

    protected function getDeprecationList(): array
    {
        return $this->listOfDeprecations;
    }

    protected function tearDown(): void
    {
        if ($this->phpunitErrorHandler !== null) {
            \restore_error_handler();
        }

        parent::tearDown();
    }
}
