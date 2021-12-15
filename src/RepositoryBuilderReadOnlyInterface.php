<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Builder\CountEntries;
use Squirrel\Entities\Builder\SelectEntries;

interface RepositoryBuilderReadOnlyInterface
{
    public function select(): SelectEntries;

    public function count(): CountEntries;
}
