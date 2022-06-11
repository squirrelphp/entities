<?php

namespace Squirrel\Entities\Tests\TestClasses;

/**
 * Example object class
 */
class ObjData
{
    public int $id;
    public string $firstName;
    public string $lastName;
    public ?string $street;
    public int $number;
    public float $floatVal;
    public bool $isGreat;
    public int $unused = 0;
    public ?string $picture;
}
