<?php

namespace Squirrel\Entities;

use Squirrel\Entities\Action\DeleteEntries;
use Squirrel\Entities\Action\InsertEntry;
use Squirrel\Entities\Action\InsertOrUpdateEntry;
use Squirrel\Entities\Action\UpdateEntries;

interface RepositoryBuilderWriteableInterface extends RepositoryBuilderReadOnlyInterface
{
    public function insert(): InsertEntry;

    public function insertOrUpdate(): InsertOrUpdateEntry;

    public function update(): UpdateEntries;

    public function delete(): DeleteEntries;
}
