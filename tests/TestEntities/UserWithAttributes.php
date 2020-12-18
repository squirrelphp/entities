<?php

namespace Squirrel\Entities\Tests\TestEntities;

use Squirrel\Entities\Annotation as SQL;
use Squirrel\Entities\PopulatePropertiesWithIterableTrait;

#[SQL\Entity("users", connection: "dada")]
class UserWithAttributes
{
    use PopulatePropertiesWithIterableTrait;

    #[SQL\Field("user_id", autoincrement: true)]
    private int $userId = 0;

    #[SQL\Field("active")]
    private bool $active = false;

    #[SQL\Field("user_name")]
    private string $userName = '';

    #[SQL\Field(name: "description", blob: true)]
    private string $description = '';

    #[SQL\Field("balance")]
    private float $balance = 0;

    #[SQL\Field("location_id")]
    private ?int $locationId;

    #[SQL\Field("create_date")]
    private int $createDate = 0;

    private string $fieldWithoutAttribute = '';

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

    public function getDescription(): string
    {
        return $this->description;
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

    public function getFieldWithoutAttribute(): string
    {
        return $this->fieldWithoutAttribute;
    }
}
