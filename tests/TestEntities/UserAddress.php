<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\Annotation\Entity;
use Squirrel\Entities\Annotation\Field;

/**
 * @Entity("users_address")
 */
class UserAddress
{
    /**
     * @Field("user_id", type="int")
     *
     * @var integer
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
     * Initialize the object with an array - not used by repository, just for testing
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return bool
     */
    public function isAtHome(): bool
    {
        return $this->atHome;
    }

    /**
     * @return string
     */
    public function getStreetName(): string
    {
        return $this->streetName;
    }

    /**
     * @return string
     */
    public function getStreetNumber(): string
    {
        return $this->streetNumber;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }
}
