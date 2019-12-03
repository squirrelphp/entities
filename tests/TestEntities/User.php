<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\Annotation as SQL;
use Squirrel\Entities\EntityConstructorTrait;

/**
 * @SQL\Entity("users")
 */
class User
{
    use EntityConstructorTrait;

    /**
     * @SQL\Field("user_id", type="int", autoincrement=true)
     *
     * @var int
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
     * @var int
     */
    private $createDate = 0;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getLoginNameMD5(): string
    {
        return $this->loginNameMD5;
    }

    public function getLoginPassword(): string
    {
        return $this->loginPassword;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getLocationId(): ?int
    {
        return $this->locationId;
    }

    public function getCreateDate(): int
    {
        return $this->createDate;
    }
}
