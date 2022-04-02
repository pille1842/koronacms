<?php

namespace App\Tests\Integration\Api;

class LoginTest extends LoggedInTestCase
{
    public function testAdminLoginWorks(): void
    {
        static::createAdminClient();
    }

    public function testRegularLoginWorks(): void
    {
        static::createRegularClient();
    }

    public function testDisabledLoginDoesntWork(): void
    {
        $client = static::createClient();

        $client->request("POST", "/api/login", [
            'json' => [
                'username' => 'disabled_johnny',
                'password' => '12345',
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'error' => 'Your user account has been disabled.',
        ]);
    }
}
