<?php

namespace Squirrel\Entities\Tests;

use Squirrel\Debug\Debug;
use Squirrel\Entities\Tests\TestEntities\User;

class EntityConstructorTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testExistingFields()
    {
        $user = new User([
            'userId' => 5,
            'active' => true,
            'userName' => 'batman',
            'loginNameMD5' => \md5('batman'),
            'loginPassword' => 'something',
            'emailAddress' => 'batman@thebatman.com',
            'balance' => 33.5,
            'locationId' => 77,
            'createDate' => 87,
        ]);

        $this->assertEquals(5, $user->getUserId());
        $this->assertEquals(true, $user->isActive());
        $this->assertEquals('batman', $user->getUserName());
        $this->assertEquals(\md5('batman'), $user->getLoginNameMD5());
        $this->assertEquals('something', $user->getLoginPassword());
        $this->assertEquals('batman@thebatman.com', $user->getEmailAddress());
        $this->assertEquals(33.5, $user->getBalance());
        $this->assertEquals(77, $user->getLocationId());
        $this->assertEquals(87, $user->getCreateDate());
    }

    public function testNonExistingField()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Property "userId5" does not exist in entity class when attempting to construct with: ' . Debug::sanitizeData(['userId5' => 5]));

        new User([
            'userId5' => 5,
        ]);
    }
}
