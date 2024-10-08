<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$sourceCodeDirectories of class Squirrel\\\\Entities\\\\Generate\\\\RepositoriesGenerateCommand constructor expects array\\<string\\>, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../bin/squirrel_repositories_generate',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#2 \\$forceFileCreation of class Squirrel\\\\Entities\\\\Generate\\\\RepositoriesGenerateCommand constructor expects bool, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../bin/squirrel_repositories_generate',
];
$ignoreErrors[] = [
	// identifier: missingType.generics
	'message' => '#^Method Squirrel\\\\Entities\\\\Attribute\\\\EntityProcessor\\:\\:getEntityFromAttribute\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/Attribute/EntityProcessor.php',
];
$ignoreErrors[] = [
	// identifier: property.onlyWritten
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\MultiSelectIterator\\:\\:\\$selectReference is never read, only written\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/MultiSelectIterator.php',
];
$ignoreErrors[] = [
	// identifier: property.onlyWritten
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\MultiSelectIterator\\:\\:\\$source is never read, only written\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/MultiSelectIterator.php',
];
$ignoreErrors[] = [
	// identifier: missingType.generics
	'message' => '#^Class Squirrel\\\\Entities\\\\Builder\\\\SelectEntries implements generic interface IteratorAggregate but does not specify its types\\: TKey, TValue$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectEntries.php',
];
$ignoreErrors[] = [
	// identifier: missingType.generics
	'message' => '#^Class Squirrel\\\\Entities\\\\Builder\\\\SelectIterator implements generic interface Iterator but does not specify its types\\: TKey, TValue$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectIterator.php',
];
$ignoreErrors[] = [
	// identifier: property.onlyWritten
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\SelectIterator\\:\\:\\$selectReference is never read, only written\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectIterator.php',
];
$ignoreErrors[] = [
	// identifier: property.onlyWritten
	'message' => '#^Property Squirrel\\\\Entities\\\\Builder\\\\SelectIterator\\:\\:\\$source is never read, only written\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/Builder/SelectIterator.php',
];
$ignoreErrors[] = [
	// identifier: method.nonObject
	'message' => '#^Cannot call method getObjectToTableFields\\(\\) on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: method.nonObject
	'message' => '#^Cannot call method getObjectTypes\\(\\) on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: method.nonObject
	'message' => '#^Cannot call method getObjectTypesNullable\\(\\) on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: method.nonObject
	'message' => '#^Cannot call method getTableName\\(\\) on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: match.unhandled
	'message' => '#^Match expression does not handle remaining value\\: mixed$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: return.type
	'message' => '#^Method Squirrel\\\\Entities\\\\MultiRepositoryReadOnly\\:\\:fetchAllAndFlatten\\(\\) should return array\\<bool\\|float\\|int\\|string\\|null\\> but returns array\\<int\\<0, max\\>, mixed\\>\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: isset.offset
	'message' => '#^Offset \'where\' on array\\{repositories\\: array, tables\\?\\: array, where\\: array, lock\\?\\: bool\\} in isset\\(\\) always exists and is not nullable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$object of method ReflectionProperty\\:\\:getValue\\(\\) expects object\\|null, mixed given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$objectOrClass of class ReflectionClass constructor expects class\\-string\\<T of object\\>\\|T of object, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/MultiRepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: foreach.nonIterable
	'message' => '#^Argument of an invalid type mixed supplied for foreach, only iterables are supported\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: offsetAccess.nonOffsetAccessible
	'message' => '#^Cannot access offset mixed on mixed\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: match.unhandled
	'message' => '#^Match expression does not handle remaining value\\: mixed$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: isset.offset
	'message' => '#^Offset \'limit\' on array\\{fields\\?\\: array\\<string\\>, field\\?\\: string, where\\?\\: array\\<int\\|string, mixed\\>, order\\?\\: array\\<int\\|string, string\\>, offset\\?\\: int, lock\\?\\: bool\\} in isset\\(\\) does not exist\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$array of function array_values expects array\\<T\\>, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$orderOptions of method Squirrel\\\\Entities\\\\RepositoryReadOnly\\:\\:preprocessOrder\\(\\) expects array\\<int\\|string, mixed\\>, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$query of method Squirrel\\\\Queries\\\\DBInterface\\:\\:fetchAll\\(\\) expects array\\{fields\\?\\: array\\<int\\|string, string\\>, field\\?\\: string, tables\\?\\: array\\<int\\|string, mixed\\>, table\\?\\: string, where\\?\\: array\\<int\\|string, mixed\\>, group\\?\\: array\\<int\\|string, string\\>, order\\?\\: array\\<int\\|string, string\\>, limit\\?\\: int, \\.\\.\\.\\}\\|string, array\\<string, mixed\\> given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$query of method Squirrel\\\\Queries\\\\DBInterface\\:\\:select\\(\\) expects array\\{fields\\?\\: array\\<int\\|string, string\\>, field\\?\\: string, tables\\?\\: array\\<int\\|string, mixed\\>, table\\?\\: string, where\\?\\: array\\<int\\|string, mixed\\>, group\\?\\: array\\<int\\|string, string\\>, order\\?\\: array\\<int\\|string, string\\>, limit\\?\\: int, \\.\\.\\.\\}\\|string, array\\<string, mixed\\> given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$value of function count expects array\\|Countable, mixed given\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$value of function floatval expects array\\|bool\\|float\\|int\\|resource\\|string\\|null, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$value of function intval expects array\\|bool\\|float\\|int\\|resource\\|string\\|null, mixed given\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$value of function strval expects bool\\|float\\|int\\|resource\\|string\\|null, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$where of method Squirrel\\\\Entities\\\\RepositoryReadOnly\\:\\:preprocessWhere\\(\\) expects array\\<int\\|string, mixed\\>, mixed given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];
$ignoreErrors[] = [
	// identifier: booleanAnd.alwaysFalse
	'message' => '#^Result of && is always false\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/../src/RepositoryReadOnly.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
