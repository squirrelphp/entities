<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Builder\DeleteEntries;
use Squirrel\Entities\Builder\InsertEntry;
use Squirrel\Entities\Builder\InsertOrUpdateEntry;
use Squirrel\Entities\Builder\UpdateEntries;

interface RepositoryBuilderWriteableInterface extends RepositoryBuilderReadOnlyInterface
{
    public function insert(): InsertEntry;

    public function insertOrUpdate(): InsertOrUpdateEntry;

    public function update(): UpdateEntries;

    public function delete(): DeleteEntries;
}
