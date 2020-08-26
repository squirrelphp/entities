<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Builder\CountEntries;

interface RepositoryBuilderReadOnlyInterface
{
    /**
     * Returns class Squirrel\Entities\Action\SelectEntries as a SELECT query builder,
     * but we omit a return docblock to avoid confusion and errors in linting, because when
     * repositories are generated we specify the exact return class
     *
     * @return mixed
     */
    public function select();

    public function count(): CountEntries;
}
