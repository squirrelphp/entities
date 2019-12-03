<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\Annotation\Entity;
use Squirrel\Entities\Annotation\Field;
use Squirrel\Entities\EntityConstructorTrait;

/**
 * @Entity("users_address")
 */
class UserAddress
{
    use EntityConstructorTrait;

    /**
     * @Field("user_id", type="int")
     *
     * @var int
     */
    private $userId = 0;

    /**
     * @Field("at_home", type="bool")
     *
     * @var bool
     */
    private $atHome = false;

    /**
     * @Field("street_name")
     *
     * @var string
     */
    private $streetName = '';

    /**
     * @Field("street_number")
     *
     * @var string
     */
    private $streetNumber = '';

    /**
     * @Field("city")
     *
     * @var string
     */
    private $city = '';

    /**
     * @Field("picture", type="blob")
     *
     * @var string
     */
    private $picture = '';

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function isAtHome(): bool
    {
        return $this->atHome;
    }

    public function getStreetName(): string
    {
        return $this->streetName;
    }

    public function getStreetNumber(): string
    {
        return $this->streetNumber;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }
}
