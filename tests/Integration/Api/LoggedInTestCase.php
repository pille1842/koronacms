<?php

namespace App\Tests\Integration\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;

class LoggedInTestCase extends ApiTestCase
{
    public static function createAdminClient(): Client
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [
            'headers' => ['content-type: application/json'],
            'json' => [
                'username' => 'admin',
                'password' => '12345',
            ],
        ]);

        self::assertResponseIsSuccessful();

        return $client;
    }

    public static function createRegularClient(): Client
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [
            'headers' => ['content-type: application/json'],
            'json' => [
                'username' => 'johndoe',
                'password' => '12345',
            ],
        ]);

        self::assertResponseIsSuccessful();

        return $client;
    }
}
