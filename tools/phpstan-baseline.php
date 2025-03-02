<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$sourceCodeDirectories of class Squirrel\\\\Entities\\\\Generate\\\\RepositoriesGenerateCommand constructor expects array\\<string\\>, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../bin/squirrel_repositories_generate',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$forceFileCreation of class Squirrel\\\\Entities\\\\Generate\\\\RepositoriesGenerateCommand constructor expects bool, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../bin/squirrel_repositories_generate',
];
$ignoreErrors[] = [
	'message' => '#^Method Squirrel\\\\Entities\\\\Attribute\\\\EntityProcessor\\:\\:getEntityFromAttribute\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/../src/Attribute/EntityProcessor.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Squirrel\\\\Entities\\\\Builder\\\\MultiSelectEntries\\:\\:getOneEntry\\(\\) should return array\\<string, mixed\\>\\|null but returns array\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/MultiSelectEntries.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Squirrel\\\\Entities\\\\Builder\\\\MultiSelectEntriesFreeform\\:\\:getOneEntry\\(\\) should return array\\<string, mixed\\>\\|null but returns array\\|null\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/MultiSelectEntriesFreeform.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Squirrel\\\\Entities\\\\Builder\\\\MultiSelectIterator\\:\\:current\\(\\) should return array\\<string, mixed\\> but returns array\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/MultiSelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\MultiSelectIterator\\:\\:\\$lastResult \\(array\\|null\\) is never assigned array so it can be removed from the property type\\.$#',
	'identifier' => 'property.unusedType',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/MultiSelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\MultiSelectIterator\\:\\:\\$selectReference \\(Squirrel\\\\Entities\\\\MultiRepositorySelectQueryInterface\\|null\\) is never assigned Squirrel\\\\Entities\\\\MultiRepositorySelectQueryInterface so it can be removed from the property type\\.$#',
	'identifier' => 'property.unusedType',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/MultiSelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\MultiSelectIterator\\:\\:\\$selectReference is never read, only written\\.$#',
	'identifier' => 'property.onlyWritten',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/MultiSelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\MultiSelectIterator\\:\\:\\$source is never read, only written\\.$#',
	'identifier' => 'property.onlyWritten',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/MultiSelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Class Squirrel\\\\Entities\\\\Builder\\\\SelectEntries implements generic interface IteratorAggregate but does not specify its types\\: TKey, TValue$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectEntries.php',
];
$ignoreErrors[] = [
	'message' => '#^Class Squirrel\\\\Entities\\\\Builder\\\\SelectIterator implements generic interface Iterator but does not specify its types\\: TKey, TValue$#',
	'identifier' => 'missingType.generics',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\SelectIterator\\:\\:\\$lastResult \\(object\\|null\\) is never assigned object so it can be removed from the property type\\.$#',
	'identifier' => 'property.unusedType',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\SelectIterator\\:\\:\\$selectReference \\(Squirrel\\\\Entities\\\\RepositorySelectQueryInterface\\|null\\) is never assigned Squirrel\\\\Entities\\\\RepositorySelectQueryInterface so it can be removed from the property type\\.$#',
	'identifier' => 'property.unusedType',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\SelectIterator\\:\\:\\$selectReference is never read, only written\\.$#',
	'identifier' => 'property.onlyWritten',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\SelectIterator\\:\\:\\$source is never read, only written\\.$#',
	'identifier' => 'property.onlyWritten',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectIterator.php',
];
$ignoreErrors[] = [
	'message' => '#^Binary operation "\\." between mixed and \'/\' results in an error\\.$#',
	'identifier' => 'binaryOp.invalid',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Binary operation "\\." between mixed and \'\\\\\\\\\' results in an error\\.$#',
	'identifier' => 'binaryOp.invalid',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Binary operation "\\." between non\\-falsy\\-string and mixed results in an error\\.$#',
	'identifier' => 'binaryOp.invalid',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'contents\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'filename\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'path\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 2,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$fileContents of method Squirrel\\\\Entities\\\\Generate\\\\FindClassesWithAttribute\\:\\:__invoke\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$filename of method Squirrel\\\\Entities\\\\Generate\\\\RepositoriesGenerateCommand\\:\\:generateRepositoryFilename\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$repositoryPhpFile of method Squirrel\\\\Entities\\\\Generate\\\\RepositoriesGenerateCommand\\:\\:repositoryFileContentsFillInBlueprint\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$namespace of method Squirrel\\\\Entities\\\\Generate\\\\RepositoriesGenerateCommand\\:\\:repositoryFileContentsFillInBlueprint\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$className of method Squirrel\\\\Entities\\\\Generate\\\\RepositoriesGenerateCommand\\:\\:repositoryFileContentsFillInBlueprint\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/Generate/RepositoriesGenerateCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Argument of an invalid type mixed supplied for foreach, only iterables are supported\\.$#',
	'identifier' => 'foreach.nonIterable',
	'count' => 7,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Binary operation "\\." between \'Unknown casting "\' and mixed results in an error\\.$#',
	'identifier' => 'binaryOp.invalid',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Binary operation "\\." between mixed and \' \' results in an error\\.$#',
	'identifier' => 'binaryOp.invalid',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Binary operation "\\." between non\\-falsy\\-string and mixed results in an error\\.$#',
	'identifier' => 'binaryOp.invalid',
	'count' => 18,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'fields\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 4,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'group\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'limit\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'lock\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'offset\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'order\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'parameters\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'query\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'tables\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'where\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset mixed on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 5,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset string on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 6,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getObjectToTableFields\\(\\) on mixed\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getObjectTypes\\(\\) on mixed\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getObjectTypesNullable\\(\\) on mixed\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getTableName\\(\\) on mixed\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:count\\(\\) should return int but returns mixed\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:fetchAll\\(\\) should return array\\<int, array\\<string, mixed\\>\\> but returns array\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:fetchAllAndFlatten\\(\\) should return array\\<bool\\|float\\|int\\|string\\|null\\> but returns list\\<mixed\\>\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'where\' on array\\{repositories\\: array, tables\\?\\: array, where\\: array, lock\\?\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'identifier' => 'isset.offset',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$array of function array_keys expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$array of function array_values expects array\\<T\\>, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$entry of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:processSelectResult\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$groupByOptions of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:preprocessGroup\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$identifier of method Squirrel\\\\Queries\\\\DBInterface\\:\\:quoteIdentifier\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$object of method ReflectionProperty\\:\\:getValue\\(\\) expects object\\|null, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$objectOrClass of class ReflectionClass constructor expects class\\-string\\<T of object\\>\\|T of object, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$orderOptions of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:preprocessOrder\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$query of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFreeform\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$query of method Squirrel\\\\Queries\\\\DBInterface\\:\\:fetchAll\\(\\) expects array\\{fields\\?\\: array\\<int\\|string, string\\>, field\\?\\: string, tables\\?\\: array\\<int\\|string, mixed\\>, table\\?\\: string, where\\?\\: array\\<int\\|string, mixed\\>, group\\?\\: array\\<int\\|string, string\\>, order\\?\\: array\\<int\\|string, string\\>, limit\\?\\: int, \\.\\.\\.\\}\\|string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$query of method Squirrel\\\\Queries\\\\DBInterface\\:\\:select\\(\\) expects array\\{fields\\?\\: array\\<int\\|string, string\\>, field\\?\\: string, tables\\?\\: array\\<int\\|string, mixed\\>, table\\?\\: string, where\\?\\: array\\<int\\|string, mixed\\>, group\\?\\: array\\<int\\|string, string\\>, order\\?\\: array\\<int\\|string, string\\>, limit\\?\\: int, \\.\\.\\.\\}\\|string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$selectOptions of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFieldSelection\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$string of function strlen expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$tables of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:preprocessJoins\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value of function count expects array\\|Countable, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 8,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$whereOptions of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:preprocessWhere\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$array of function implode expects array\\|null, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$objectToTableFields of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFieldSelection\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$objectToTableFields of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:preprocessGroup\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$objectToTableFields of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:preprocessOrder\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$objectToTableFields of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:preprocessWhere\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$selectTypes of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:processSelectResults\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$tableName of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFreeform\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$tableNames of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:preprocessJoins\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$types of class Squirrel\\\\Entities\\\\MultiRepositorySelectQuery constructor expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$vars of method Squirrel\\\\Queries\\\\DBInterface\\:\\:fetchAll\\(\\) expects array\\<int, mixed\\>, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$vars of method Squirrel\\\\Queries\\\\DBInterface\\:\\:select\\(\\) expects array\\<int, mixed\\>, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$objectToTableFields of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFreeform\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$objectToTableFields of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:preprocessJoins\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$objectTypes of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFieldSelection\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$selectTypesNullable of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:processSelectResults\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$typesNullable of class Squirrel\\\\Entities\\\\MultiRepositorySelectQuery constructor expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#4 \\$objectTypesNullable of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFieldSelection\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'parameters\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryWriteable.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset \'query\' on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryWriteable.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$query of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFreeform\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryWriteable.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$tableName of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFreeform\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryWriteable.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$vars of method Squirrel\\\\Queries\\\\DBInterface\\:\\:change\\(\\) expects array\\<int, mixed\\>, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryWriteable.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#3 \\$objectToTableFields of method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:buildFreeform\\(\\) expects array, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryWriteable.php',
];
$ignoreErrors[] = [
	'message' => '#^Argument of an invalid type mixed supplied for foreach, only iterables are supported\\.$#',
	'identifier' => 'foreach.nonIterable',
	'count' => 2,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Binary operation "\\." between non\\-falsy\\-string and mixed results in an error\\.$#',
	'identifier' => 'binaryOp.invalid',
	'count' => 2,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access offset mixed on mixed\\.$#',
	'identifier' => 'offsetAccess.nonOffsetAccessible',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Squirrel\\\\Entities\\\\RepositoryReadOnly\\:\\:convertNameToTable\\(\\) should return string but returns mixed\\.$#',
	'identifier' => 'return.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Offset \'limit\' on array\\{fields\\?\\: array\\<string\\>, field\\?\\: string, where\\?\\: array\\<int\\|string, mixed\\>, order\\?\\: array\\<int\\|string, string\\>, offset\\?\\: int, lock\\?\\: bool\\} in isset\\(\\) does not exist\\.$#',
	'identifier' => 'isset.offset',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$array of function array_values expects array\\<T\\>, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$identifier of method Squirrel\\\\Queries\\\\DBInterface\\:\\:quoteIdentifier\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of method ReflectionClass\\<object\\>\\:\\:getProperty\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$orderOptions of method Squirrel\\\\Entities\\\\RepositoryReadOnly\\:\\:preprocessOrder\\(\\) expects array\\<int\\|string, mixed\\>, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$query of method Squirrel\\\\Queries\\\\DBInterface\\:\\:fetchAll\\(\\) expects array\\{fields\\?\\: array\\<int\\|string, string\\>, field\\?\\: string, tables\\?\\: array\\<int\\|string, mixed\\>, table\\?\\: string, where\\?\\: array\\<int\\|string, mixed\\>, group\\?\\: array\\<int\\|string, string\\>, order\\?\\: array\\<int\\|string, string\\>, limit\\?\\: int, \\.\\.\\.\\}\\|string, array\\<string, mixed\\> given\\.$#',
	'identifier' => 'argument.type',
	'count' => 2,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$query of method Squirrel\\\\Queries\\\\DBInterface\\:\\:select\\(\\) expects array\\{fields\\?\\: array\\<int\\|string, string\\>, field\\?\\: string, tables\\?\\: array\\<int\\|string, mixed\\>, table\\?\\: string, where\\?\\: array\\<int\\|string, mixed\\>, group\\?\\: array\\<int\\|string, string\\>, order\\?\\: array\\<int\\|string, string\\>, limit\\?\\: int, \\.\\.\\.\\}\\|string, array\\<string, mixed\\> given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value of function count expects array\\|Countable, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 3,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$value of function intval expects array\\|bool\\|float\\|int\\|resource\\|string\\|null, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$where of method Squirrel\\\\Entities\\\\RepositoryReadOnly\\:\\:preprocessWhere\\(\\) expects array\\<int\\|string, mixed\\>, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#2 \\$fieldName of method Squirrel\\\\Entities\\\\RepositoryReadOnly\\:\\:castObjVariable\\(\\) expects string, mixed given\\.$#',
	'identifier' => 'argument.type',
	'count' => 2,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	'message' => '#^Result of && is always false\\.$#',
	'identifier' => 'booleanAnd.alwaysFalse',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
