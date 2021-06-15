Squirrel Entities Component
===========================

[![Build Status](https://img.shields.io/travis/com/squirrelphp/entities.svg)](https://travis-ci.com/squirrelphp/entities) [![Test Coverage](https://api.codeclimate.com/v1/badges/36a9f5a3b4abbaf7901c/test_coverage)](https://codeclimate.com/github/squirrelphp/entities/test_coverage) ![PHPStan](https://img.shields.io/badge/style-level%208-success.svg?style=flat-round&label=phpstan) [![Packagist Version](https://img.shields.io/packagist/v/squirrelphp/entities.svg?style=flat-round)](https://packagist.org/packages/squirrelphp/entities) [![PHP Version](https://img.shields.io/packagist/php-v/squirrelphp/entities.svg)](https://packagist.org/packages/squirrelphp/entities) [![Software License](https://img.shields.io/badge/license-MIT-success.svg?style=flat-round)](LICENSE)

Simple & safe implementation of handling SQL entities and repositories as well as multi-table SQL queries while staying lightweight and easy to understand and use. Offers rapid application development by generating repositories (which should not be added to VCS) for entities, and [squirrelphp/entities-bundle](https://github.com/squirrelphp/entities-bundle) offers automatic integration of these repositories into Symfony.

This library builds upon [squirrelphp/queries](https://github.com/squirrelphp/queries) and works in a similar way: the interfaces, method names and the query builder look and feel almost the same and are just at a higher level with defined entities.

Installation
------------

    composer require squirrelphp/entities

Table of contents
-----------------

- [Creating entities](#creating-entities)
- [Creating repositories](#creating-repositories)
- [Using repositories](#using-repositories)
- [Multi repository queries](#multi-repository-queries)
- [Transactions](#transactions)
- [More complex column types](#more-complex-column-types)
- [Read-only entity objects](#read-only-entity-objects)
- [Recommendations on how to use this library](#recommendations-on-how-to-use-this-library)

Creating entities
-----------------

### Defining an entity with annotations

If you have used an ORM like Doctrine this will feel similar at first, although the functionality is different. Below is an example how an entity can be defined via attributes:

```php
namespace Application\Entity;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;

#[Entity("users")]
class User
{
    #[Field("user_id", autoincrement: true)]
    private int $userId;

    #[Field("active")]
    private bool $active;

    #[Field("street_name")]
    private ?string $streetName;

    #[Field("street_number")]
    private ?string $streetNumber;

    #[Field("city")]
    private string $city;

    #[Field("balance")]
    private float $balance;

    #[Field("picture_file", blob: true)]
    private ?string $picture;

    #[Field("visits")]
    private int $visitsNumber;
}
```

The class is defined as an entity with the table name, and each class property is defined as a table field with the column name in the database, where the type is taken from the PHP property type (string, int, float, bool). If the property type is nullable, the column type is assumed to be nullable too. You can also define if it is an autoincrement column (called SERIAL in Postgres) and if it is a blob column (binary large object, called "blob" in most databases or "bytea" in Postgres).

Whether the class properties are private, protected or public does not matter, you can choose whatever names you want, and you can design the rest of the class however you want. You can even make the classes read-only, by having private properties and only defining getters - see [Read-only entity objects](#read-only-entity-objects) for more details on why you would want to do that.

### Defining an entity directly

This is not currently recommended, but if you are not using [squirrelphp/entities-bundle](https://github.com/squirrelphp/entities-bundle) and want to manually configure/create entities, you can create RepositoryConfig objects - with entities-bundle these are created automatically for you. The annotations in the `User` example in the last section would be equivalent to this RepositoryConfig definition:

```php
$repositoryConfig = new \Squirrel\Entities\RepositoryConfig(
    '', // connectionName, none defined
    'users', // tableName
    [   // tableToObjectFields, mapping table column names to object property names
        'user_id' => 'userId',
        'active' => 'active',
        'street_name' => 'streetName',
        'street_number' => 'streetNumber',
        'city' => 'city',
        'balance' => 'balance',
        'picture_file' => 'picture',
        'visits' => 'visitsNumber',
    ],
    [   // objectToTableFields, mapping object property names to table column names
        'userId' => 'user_id',
        'active' => 'active',
        'streetName' => 'street_name',
        'streetNumber' => 'street_number',
        'city' => 'city',
        'balance' => 'balance',
        'picture' => 'picture_file',
        'visitsNumber' => 'visits',
    ],
    \Application\Entity\User::class, // object class
    [   // objectTypes, which class properties should have which database type
        'userId' => 'int',
        'active' => 'bool',
        'streetName' => 'string',
        'streetNumber' => 'string',
        'city' => 'string',
        'balance' => 'float',
        'picture' => 'blob',
        'visitsNumber' => 'int',
    ],
    [   // objectTypesNullable, which fields can be NULL
        'userId' => false,
        'active' => false,
        'streetName' => true,
        'streetNumber' => true,
        'city' => false,
        'balance' => false,
        'picture' => true,
        'visitsNumber' => false,
    ],
    'user_id' // Table field name of the autoincrement column - if there is none this is an empty string
);
```

Creating repositories
---------------------

### Base repositories

You need to define repositories for each entity in order to use them, and create RepositoryConfig classes from the annotated classes ([squirrelphp/entities-bundle](https://github.com/squirrelphp/entities-bundle) does this for you, so you don't need to care too much about these steps).

The repositories only need a `DBInterface` service (from [squirrelphp/queries](https://github.com/squirrelphp/queries)) and the RepositoryConfig. There are read-only repositories and writeable repositories so you can more easily restrict where and how your data gets changed. These are the base repository classes:

- Squirrel\Entities\RepositoryReadOnly
- Squirrel\Entities\RepositoryWriteable

They offer almost the same functionality as `DBInterface` in `squirrelphp/queries`, but they do additional steps to avoid mistakes:

- You use the object names in your queries, not the column names in the table, the library then converts them for the query
- All provided values are converted to the appropriate type (string, int, bool, float, blob)
- If any unknown column names are used an exception is thrown
- Whenever you do a SELECT query to retrieve entries, you get entity objects back instead of arrays, and all values are correctly converted before putting them in the entity objects

This makes it hard to write invalid queries which are not identified as such before executing them, and removes the need to do any tedious type conversions.

### Builder repositories

Although you can use the base repositories directly, it is usually easier and more readable to use builder repositories to build your queries - these are very similar to the query builder in [squirrelphp/queries](https://github.com/squirrelphp/queries).

#### Generating builder repositories

Builder repositories need to be generated for all entities, in order to have proper type hints (for easier coding and static analyzers) and to have individual classes for all entities to use in dependency injection.

You can use the squirrel_repositories_generate command in this library to generate the repositories and .gitignore files automatically - run it like this:

    vendor/bin/squirrel_repositories_generate --source-dir=src

You can define multiple source-dirs:

    vendor/bin/squirrel_repositories_generate --source-dir=src/Entity --source-dir=src/Domain/Entity

Whenever an entity with the library attributes is found, the following files are created in the same directory of the annotated entity:

- RepositoryReadOnly builder class, by adding `RepositoryReadOnly` to the entity class name
- RepositoryWriteable builder class, by adding `RepositoryWriteable` to the entity class name
- A .gitignore file which ignores both the .gitignore file itself and all generated builder classes

This means you do not need to ever edit these generated repositories and should never commit them to git. They are there to help you, not to burden you.

Our entity example `User` would generate the following classes in the same directory as the entity:

- Application\Entity\UserRepositoryReadOnly with the filename UserRepositoryReadOnly.php
- Application\Entity\UserRepositoryWriteable with the filename UserRepositoryWriteable.php
- .gitignore file listing .gitignore, UserRepositoryReadOnly.php and UserRepositoryWriteable.php

Using repositories
------------------

As a stark difference to "normal" ORMs, the entity class is only used when getting results from the database, not to write any changes to the database, which is why it does not matter how you use or design the entity classes except for the needed annotations.

All examples use the generated builder repositories, not the base repositories. To see all possibilities and more details you can look at `Squirrel\Entities\RepositoryBuilderReadOnlyInterface` and `Squirrel\Entities\RepositoryBuilderWriteableInterface`

### Retrieving database entries as objects

```php
$users = $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
    ->select()
    ->where([
        'active' => true,
        'userId' => [5, 77, 186],
    ])
    ->orderBy([
        'balance' => 'DESC',
    ])
    ->getAllEntries();

foreach ($users as $user) {
    // Each $user entry is an instance of Application\Entity\User
}
```

If you only need certain fields in the table, you can define those you want explicitely:

```php
$users = $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
    ->select()
    ->fields([
        'userId',
        'active',
        'city',
    ])
    ->where([
        'active' => true,
        'userId' => [5, 77, 186],
    ])
    ->orderBy([
        'balance' => 'DESC',
    ])
    ->getAllEntries();

foreach ($users as $user) {
    // Only 'userId', 'active' and 'city' have been populated in the entity instances
}
```

Or if you only want a list of user IDs, you can get only those with `getFlattenedFields`:

```php
$userIds = $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
    ->select()
    ->fields([
        'userId',
    ])
    ->where([
        'active' => true,
        'userId' => [5, 77, 186],
    ])
    ->orderBy([
        'balance' => 'DESC',
    ])
    ->getFlattenedFields();

foreach ($userIds as $userId) {
    // Each $userId is an integer with the user ID
}
```

If you want to get one entry after another, you can use the select builder as an iterator:

```php
$userBuilder = $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
    ->select()
    ->where([
        'active' => true,
        'userId' => [5, 77, 186],
    ])
    ->orderBy([
        'balance' => 'DESC',
    ]);

foreach ($userBuilder as $user) {
    // The query is executed when the foreach loop starts,
    // and one entry after another is retrieved until no more results exist
}
```

Or if you just need exactly one entry, you can use `getOneEntry':

```php
$user = $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
    ->select()
    ->where([
        'userId' => 13,
    ])
    ->getOneEntry();

// $user is now either null, if the entry was not found,
// or an instance of Application\Entity\User
```

If the SELECT query is done within a transaction, you might want to block the retrieved entries so they aren't changed by another query before the transaction finishes - you can use `blocking()` for that:

```php
$user = $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
    ->select()
    ->where([
        'userId' => 13,
    ])
    ->blocking()
    ->getOneEntry();
```

The SELECT query is done with ` ... FOR UPDATE` at the end in the above query.

### Counting the number of entries

Often you just want to know how many entries there are, which is where `count` comes in:

```php
$activeUsersNumber = $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
    ->count()
    ->where([
        'active' => true,
    ])
    ->getNumber();

// $activeUsersNumber is an integer
if ($activeUsersNumber === 0) {
    throw new \Exception('No users found!');
}
```

You can block changes to the counted entries by using `blocking()` and putting the count query within a transaction, although this can easily lock many entries in a table and lead to deadlocks - use it cautiously.

### Adding a new entry (INSERT)

```php
$newUserId = $userRepositoryWriteable // \Application\Entity\UserRepositoryWriteable instance
    ->insert()
    ->set([
        'active' => true,
        'city' => 'London',
        'balance' => 500,
    ])
    ->writeAndReturnNewId();
```

`writeAndReturnNewId` only works if you have specified an autoincrement column - use `write` otherwise.

### Updating an existing entry (UPDATE)

```php
$foundRows = $userRepositoryWriteable // \Application\Entity\UserRepositoryWriteable instance
    ->update()
    ->set([
        'active' => false,
        'city' => 'Paris',
    ])
    ->where([
        'userId' => 5,
    ])
    ->writeAndReturnAffectedNumber();
```

The number of affected rows is just the rows which match the WHERE clause in the database. You can use `write` instead if you are not interested in this number.

You can do an UPDATE without a WHERE clause, updating all entries in the table, but you need to explicitely tell the builder because we want to avoid accidental "UPDATE all" queries:

```php
$userRepositoryWriteable // \Application\Entity\UserRepositoryWriteable instance
    ->update()
    ->set([
        'active' => false,
    ])
    ->confirmNoWhereRestrictions()
    ->write();
```

### Insert entry if it does not exist, update it otherwise (UPSERT - insertOrUpdate)

Insert an entry if it does not exist yet, or otherwise update the existing entry. This functionality exists because it can be executed as one atomic query in the database, making it faster and more efficient than doing your own separate queries in a transaction. It is commonly known as UPSERT (update-or-insert), for MySQL with the syntax `INSERT ... ON DUPLICATE KEY UPDATE ...` and for Postgres/SQLite with `INSERT ... ON CONFLICT (index_columns) DO UPDATE SET ...`.

```php
$userRepositoryWriteable // \Application\Entity\UserRepositoryWriteable instance
    ->insertOrUpdate()
    ->set([
        'userId' => 5,
        'active' => true,
        'city' => 'Paris',
        'balance' => 500,
    ])
    ->index('userId')
    ->write();
```

You need to provide the columns which form a unique index with the `index` method. If the row does not exist yet it is inserted, and if it does exist, the values in `set` are updated. You can change the UPDATE part though:

```php
$userRepositoryWriteable // \Application\Entity\UserRepositoryWriteable instance
    ->insertOrUpdate()
    ->set([
        'userId' => 5,
        'active' => true,
        'city' => 'Paris',
        'balance' => 500,
    ])
    ->index('userId')
    ->setOnUpdate([
        'balance' => 500,
    ])
    ->write();
```

This would insert the row with all the provided values, but if it already exists only `balance` is changed, not `city` and `active`. Making a custom UPDATE part can make a lot of sense if you want to just increase a number by one:

```php
$userRepositoryWriteable // \Application\Entity\UserRepositoryWriteable instance
    ->insertOrUpdate()
    ->set([
        'userId' => 5,
        'active' => true,
        'visitsNumber' => 1,
    ])
    ->index('userId')
    ->setOnUpdate([
        ':visitsNumber: = :visitsNumber: + 1',
    ])
    ->write();
```

This would create the user entry with one visit, or if it exists just increase `visitsNumber` by one.

### Delete an existing entry

```php
$deletedNumber = $userRepositoryWriteable // \Application\Entity\UserRepositoryWriteable instance
    ->delete()
    ->where([
        'active' => true,
    ])
    ->writeAndReturnAffectedNumber();
```

This would delete all active users and return the number of rows which were deleted as an integer. If you are not interested in the number of deleted entries you can call the `write` method instead.

You can delete all entries in a table, but you have to make it explicit to avoid accidentally forgetting WHERE restrictions and removing all data (similar to the update method where you need to confirm no where restrictions too):

```php
$userRepositoryWriteable // \Application\Entity\UserRepositoryWriteable instance
    ->delete()
    ->confirmNoWhereRestrictions()
    ->write();
```

Multi repository queries
------------------------

Sometimes you might want to do queries where multiple entities are involved (or the same entity multiple times), which is where the MultiRepository classes come in. Like with regular repositories there are base repositories and builder repositories, but unlike the regular repositories they have no configuration of their own - they take all the necessary data from the involved repositories.

All the examples are for the builder repositories, as they are easier to explain and use. We use the User entity again, and an additional entity called `Visit` with the following definition:

```php
namespace Application\Entity;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;

#[Entity("users_visits")]
class Visit
{
    #[Field("visit_id", autoincrement: true)]
    private int $visitId = 0;

    #[Field("user_id")]
    private int $userId = 0;

    #[Field("created_timestamp")]
    private int $timestamp = 0;
}
```

### Select queries

```php
$multiBuilder = new \Squirrel\Entities\MultiRepositoryBuilderReadOnly();

$entries = $multiBuilder
    ->select()
    ->fields([
        'user.userId',
        'user.active',
        'visit.timestamp',
    ])
    ->inRepositories([
        'user' => $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
        'visit' => $visitRepositoryReadOnly // \Application\Entity\VisitRepositoryReadOnly instance
    ])
    ->where([
        ':user.userId: = :visit.userId:',
        'user.userId' => 5,
    ])
    ->orderBy([
        'visit.timestamp' => 'DESC',
    ])
    ->limitTo(10)
    ->getAllEntries();

foreach ($entries as $entry) {
    // Each $entry has the following data in it:
    // - $entry['user.userId'] as an integer
    // - $entry['user.active'] as a boolean
    // - $entry['visit.timestamp'] as an integer
}
```

You can rename the returned fields:

```php
$entries = $multiBuilder
    ->select()
    ->fields([
        'userId' => 'user.userId',
        'isActive' => 'user.active',
        'visitTimestamp' => 'visit.timestamp',
    ])
    ->inRepositories([
        'user' => $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
        'visit' => $visitRepositoryReadOnly // \Application\Entity\VisitRepositoryReadOnly instance
    ])
    ->where([
        ':user.userId: = :visit.userId:',
        'user.userId' => 5,
    ])
    ->getAllEntries();

foreach ($entries as $entry) {
    // Each $entry has the following data in it:
    // - $entry['userId'] as an integer
    // - $entry['isActive'] as a boolean
    // - $entry['visitTimestamp'] as an integer
}
```

You can define your own way of joining the entity tables, group the entries and make the SELECT blocking. This example uses all these possibilities:

```php
$multiBuilder = new \Squirrel\Entities\MultiRepositoryBuilderReadOnly();

$entries = $multiBuilder
    ->select()
    ->fields([
        'visit.userId',
        'visit.timestamp',
        'userIdWhenActive' => 'user.userId',
    ])
    ->inRepositories([
        'user' => $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
        'visit' => $visitRepositoryReadOnly // \Application\Entity\VisitRepositoryReadOnly instance
    ])
    ->joinTables([
        ':visit: LEFT JOIN :user: ON (:user.userId: = :visit.userId: AND :user.active: = ?)' => true,
    ])
    ->where([
        ':visit.timestamp: > ?' => time() - 86400, // Visit timestamp within the last 24 hours
    ])
    ->groupBy([
        'visit.userId',
    ])
    ->orderBy([
        'visit.timestamp' => 'DESC',
    ])
    ->limitTo(5)
    ->startAt(10)
    ->blocking()
    ->getAllEntries();

foreach ($entries as $entry) {
    // Each $entry has the following data in it:
    // - $entry['visit.userId'] as an integer
    // - $entry['visit.timestamp'] as an integer
    // - $entry['userIdWhenActive'] as an integer if the LEFT JOIN was successful, otherwise NULL
}
```

Just like with the select builder of singular repositories you can retrieve results via `getAllEntries`, `getOneEntry`, `getFlattenedFields` or by iterating over the builder:

```php
$selectBuilder = $multiBuilder
    ->select()
    ->fields([
        'userId' => 'user.userId',
        'isActive' => 'user.active',
        'visitTimestamp' => 'visit.timestamp',
    ])
    ->inRepositories([
        'user' => $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
        'visit' => $visitRepositoryReadOnly // \Application\Entity\VisitRepositoryReadOnly instance
    ])
    ->where([
        ':user.userId: = :visit.userId:',
        'user.userId' => 5,
    ]);

foreach ($selectBuilder as $entry) {
    // Each $entry has the following data in it:
    // - $entry['userId'] as an integer
    // - $entry['isActive'] as a boolean
    // - $entry['visitTimestamp'] as an integer
}
```

### Count queries

```php
$entriesNumber = $multiBuilder
    ->count()
    ->inRepositories([
        'user' => $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
        'visit' => $visitRepositoryReadOnly // \Application\Entity\VisitRepositoryReadOnly instance
    ])
    ->where([
        ':user.userId: = :visit.userId:',
        'user.userId' => 5,
    ])
    ->getNumber();

// $entriesNumber now contains the number of visits of userId = 5
```

### Freeform select queries

Sometimes you might want to create a more complex SELECT query, for example with subqueries or other functionality that is not directly supported by the multi repository select builder. Freeform queries give you that freedom, although it is recommended to use them sparingly, as they cannot be checked as rigorously as regular queries and they are more likely to only work for a specific database system (as there are often syntax/behavior differences between vendors). Using vendor-specific functionality might be a good use case for freeform queries, as long as you keep in mind that you are writing non-portable SQL.

```php
$entries = $multiBuilder
    ->selectFreeform()
    ->fields([
        'userId' => 'user.userId',
        'isActive' => 'user.active',
    ])
    ->inRepositories([
        'user' => $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
        'visit' => $visitRepositoryReadOnly // \Application\Entity\VisitRepositoryReadOnly instance
    ])
    ->queryAfterFROM(':user: WHERE :user.userId: = ? AND NOT EXISTS ( SELECT * FROM :visit: WHERE :user.userId: = :visit.userId: )')
    ->withParameters([5])
    ->confirmFreeformQueriesAreNotRecommended('OK')
    ->getAllEntries();

foreach ($entries as $entry) {
    // Each $entry has the following data in it:
    // - $entry['userId'] as an integer
    // - $entry['isActive'] as a boolean
}
```

Getting and casting the fields is done in the same way as with a fully structured select query, but everything after `SELECT ... FROM` can be freely defined - how the tables are joined, what is checked, etc. You need to call `confirmFreeformQueriesAreNotRecommended` with 'OK' in the query builder to make it clear that you have made a conscious decision to use freeform queries.

### Freeform update queries

Freeform update queries are not recommended either, but sometimes you might have no other way of executing a query, and having full freedom can enable queries which are much more efficient than doing multiple other queries / multiple UPDATEs. The general way it works is by defining `query` and `withParameters`:

```php
$multiBuilder
    ->updateFreeform()
    ->inRepositories([
        'user' => $userRepositoryReadOnly // \Application\Entity\UserRepositoryReadOnly instance
        'visit' => $visitRepositoryReadOnly // \Application\Entity\VisitRepositoryReadOnly instance
    ])
    ->query('UPDATE :user:, :visit: SET :visit.timestamp: = ? WHERE :user.userId: = :visit.userId: AND :user.userId: = ?')
    ->withParameters([time(), 5])
    ->confirmFreeformQueriesAreNotRecommended('OK')
    ->write();
```

The above query also shows the main drawback of multi table UPDATE queries - they are almost never portable to other database systems (because they are not part of the SQL standard), as the above query would work for MySQL, but would fail for Postgres or SQLite, as they have a different syntax / different restrictions. In many cases this might be fine if you get a real benefit from having such a custom query.

You can use `writeAndReturnAffectedNumber` (instead of using `write`) to find out how many entries were found for the UPDATE. You need to call `confirmFreeformQueriesAreNotRecommended` with 'OK' in the query builder to make it clear that you have made a conscious decision to use freeform queries.

Transactions
------------

It is usually important for multiple queries to be executed within a transaction, especially when something is changed, to make sure all changes are done atomically. Using repositories this is easy by using the `Transaction` class:

```php
use Squirrel\Entities\Transaction;

// Transaction class checks that all involved repositories use
// the same database connection so a transaction is actually possible
$transactionHandler = Transaction::withRepositories([
    $userRepositoryWriteable, // \Application\Entity\UserRepositoryWriteable instance
    $visitRepositoryReadOnly, // \Application\Entity\VisitRepositoryReadOnly instance
]);

// Run method takes a callable and lets you define any additional arguments
// which are then passed along
$transactionHandler->run(
    function (
        int $userId,
        \Application\Entity\UserRepositoryWriteable $userRepositoryWriteable,
        \Application\Entity\VisitRepositoryReadOnly $visitRepositoryReadOnly
    ) {
        $visitsNumber = $visitRepositoryReadOnly
            ->count()
            ->where([
                'userId' => $userId,
            ])
            ->blocking()
            ->getNumber();

        $userRepositoryWriteable
            ->update()
            ->set([
                'visitsNumber' => $visitsNumber,
            ])
            ->where([
                'userId' => $userId,
            ])
            ->write();
    },
    5, // first argument passed to callable ($userId)
    $userRepositoryWriteable, // second argument passed to callable
    $visitRepositoryReadOnly // third argument passed to callable
);
```

The advantage of the static `withRepositories` function is that you cannot do anything wrong without it throwing a `DBInvalidOptionException` - no invalid repositories, no different connections, etc. Internally the `Transaction` class uses class reflection to check the data and expects either `RepositoryBuilderReadOnly` instances or `RepositoryReadOnly` instances (or the `Writeable` instead of `ReadyOnly` versions).

You can easily create Transaction objects yourself by just passing in an object implementing `DBInterface` (from [squirrelphp/queries](https://github.com/squirrelphp/queries)). When using the class in that way you will need to make sure yourself that all involved repositories/queries use the same connection.

More complex column types
-------------------------

Only `string`, `int`, `bool`, `float` and `blob` are supported as column types, yet databases support many specialized column types - like dates, times, geographic positions, IP addresses, enumerated values, JSON, etc.

You should have no problems supporting such special types, but because this library is kept simple, it only supports the basic PHP types and it will be your responsibility to use or convert them to any other types, according to the needs of your application.

Below is a modification of our existing example to show how you could handle non-trivial column types:

```php
namespace Application\Entity;

use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;

#[Entity("users")]
class User
{
    #[Field("user_id", autoincrement: true)]
    private int $userId = 0;

    #[Field("active")]
    private bool $active = false;

    /**
     * @var string JSON data in the database
     */
    #[Field("note_data")]
    private string $notes = '';

    /**
     * @var string datetime in the database
     */
    #[Field("created")]
    private string $createDate = '';

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getNotes(): array
    {
        return \json_decode($this->notes, true);
    }

    public function getCreateDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->createDate, new \DateTimeZone('Europe/London'));
    }
}
```

The entity class converts between the database and the application to give the application something it can work with in a format it understands. If squirrelphp would handle these conversions it could quickly go wrong - even something seemingly simple like a date is not self-explanatory, it always needs a time zone it is relative too.

You should use the freedom of choosing how to convert your database values to application values by using value objects where it makes sense - we used `DateTimeImmutable` as a value object, but it can be custom:

```php
namespace Application\Value;

class GeoPoint
{
    private float $lat = 0;
    private float $lng = 0;

    public function __construct(float $lat, float $lng)
    {
      $this->lat = $lat;
      $this->lng = $lng;
    }

    public function getLatitude(): float
    {
        return $this->lat;
    }

    public function getLongitude(): float
    {
        return $this->lng;
    }
}
```

```php
namespace Application\Entity;

use Application\Value\GeoPoint;
use Squirrel\Entities\Attribute\Entity;
use Squirrel\Entities\Attribute\Field;

#[Entity("users_locations")]
class UserLocation
{
    #[Field("user_id")]
    private int $userId = 0;

    /**
     * @var string "point" in Postgres
     */
    #[Field("location")]
    private string $locationPoint = '';

    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Convert the point syntax from the database into a value object
     */
    public function getLocation(): GeoPoint
    {
        $point = \explode(',', \trim($this->locationPoint, '()'));

        return new GeoPoint($point[0], $point[1]);
    }
}
```

Here a `point` data type from Postgres is used as an example, which is then converted into the `GeoPoint` value object so the application can easily pass it around and use it. Custom data types are usually received as strings by the application and can then be processed however you want.

Beware that using database-specific column types will make it harder to change database systems / make your entities and SQL code be vendor-specific. It might still be worth it to use these column types, but you should be aware of it.

Read-only entity objects
------------------------

Because this library separates reads and writes and only uses objects for reads, the entity objects do not need to be mutable - they can be immutable and read-only. If you have used other ORMs this might seem counterintuitive at first (because most ORMs are built around mutable entity objects), but it does offer new possibilities:

- Immutable entity objects can be passed to templates or any services without the risk of them being changed or having any side effects on other parts of the application
- Caching is easy: use whatever technique you like to cache entity objects if this becomes necessary, because they are just data / plain objects
- Entity classes can be more about the logic of the entity (how to use it), offering additional functionality and leaving out all the database functionality
- You can create interfaces for your entity objects to separate infrastructure and entity logic, defining the methods on an entity you want to provide independent of what data is in the database (and you can create aggregates/root entities with multiple read-only entities, the possibilities for more abstractions are plentiful)
- The library does not need to keep track of your entities, lazy-loading parts of them, and doing complicated things you don't really understand: everything that happens is straightforward and within your control
- You still have the advantages of using objects to access the data instead of using unstructured data, so you can clearly define the types you are using, or implement methods returning value objects, and the code can be checked by static analyzers

Having the writing queries so clearly separated from the objects also offers advantages:

- It is easy to spot where something is being changed, just look for where `EntityNameRepositoryWriteable` classes are used, and only use the Writeable classes where you actually write to the repository and `EntityNameRepositoryReadOnly` everywhere else
- Queries are not completely abstracted away from you, leaving it up to you to make simple or complicated queries according to your needs, instead of having to hope that the library creates efficient queries for you and forgetting how the data is stored and manipulated
- You can change many rows at once, or do multi repository queries with a straightforward syntax and without learning a new query language
- Using a command bus, or doing CQRS (Command Query Responsibility Segregation) in your application becomes an easy pattern to follow, because you always define if you are writing to or reading from a repository

Recommendations on how to use this library
------------------------------------------

The following recommendations are slightly opinionated ways of using this library - you don't have to use the library this way:

- Make your entity objects read-only / immutable, by defining all properties as private and never changing them, and only offer methods to get data, no setters and no keeping track of changes
- Create a distinct class for every use-case in your application which changes something in your application, for example `OrderCreateHandler` or `UserPasswordChangeHandler`, and have one public method to execute that class
- Put all these classes in their own directory, like `ChangeHandler`, and make sure you only ever use the `RepositoryWriteable` classes within such a directory, to avoid unintential changes
- If the change is not super simple, create an action object (usually just a simple value object with public properties) for each handler, like `OrderCreateAction`, and make sure that object can be validated and then passed to the change handler
- You can put those action objects in a directory like `ChangeAction`, or put them in the same directory as the change handlers
- Only use the generated builder repositories to access and change your entities, and use `Transaction::withRepositories` for transactions
- Only use multi repository queries when it is a conscious decision and there are no good alternatives
- Use [squirrelphp/entities-bundle](https://github.com/squirrelphp/entities-bundle) and Symfony, which autoconfigures almost everything for you