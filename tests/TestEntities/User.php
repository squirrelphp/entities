<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\Annotation as SQL;

/**
 * @SQL\Entity("users")
 */
class User
{
    /**
     * @SQL\Field("user_id", type="int")
     *
     * @var integer
     */
    private $userId = 0;

    /**
     * @SQL\Field("active", type="bool")
     *
     * @var bool
     */
    private $active = false;

    /**
     * @SQL\Field("user_name")
     *
     * @var string
     */
    private $userName = '';

    /**
     * @SQL\Field("login_name_md5")
     *
     * @var string
     */
    private $loginNameMD5 = '';

    /**
     * @SQL\Field("login_password")
     *
     * @var string
     */
    private $loginPassword = '';

    /**
     * @SQL\Field("email_address")
     *
     * @var string
     */
    private $emailAddress = '';

    /**
     * @SQL\Field("balance", type="float")
     *
     * @var float
     */
    private $balance = 0;

    /**
     * @SQL\Field("location_id", type="int", nullable=true)
     *
     * @var int|null
     */
    private $locationId;

    /**
     * @SQL\Field("create_date", type="int")
     *
     * @var integer
     */
    private $createDate = 0;

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
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @return string
     */
    public function getLoginNameMD5(): string
    {
        return $this->loginNameMD5;
    }

    /**
     * @return string
     */
    public function getLoginPassword(): string
    {
        return $this->loginPassword;
    }

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @return int|null
     */
    public function getLocationId(): ?int
    {
        return $this->locationId;
    }

    /**
     * @return int
     */
    public function getCreateDate(): int
    {
        return $this->createDate;
    }
}
