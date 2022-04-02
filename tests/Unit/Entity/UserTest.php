<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCanCreateObject(): void
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testGettersAndSetters(): void
    {
        $user = new User();
        $user->setUsername('Test');
        $user->setPassword('12345');
        $user->setRoles(['ROLE_USER']);
        $user->setIsEnabled(true);

        $this->assertEquals('Test', $user->getUsername());
        // Test that the identifier is the username
        $this->assertEquals('Test', $user->getUserIdentifier());
        $this->assertEquals('12345', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertEquals(true, $user->getIsEnabled());
    }
}
