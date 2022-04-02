<?php

namespace App\Tests\Integration\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManager;

class UserTest extends LoggedInTestCase
{
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }

    public function testGetCollection(): void
    {
        $client = static::createRegularClient();

        $response = $client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@id' => '/api/users',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view' => [
                '@id' => '/api/users?page=1',
                '@type' => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/users?page=1',
                'hydra:last' => '/api/users?page=4',
                'hydra:next' => '/api/users?page=2',
            ]
        ]);
        $this->assertCount(30, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(User::class);

        // Test that unauthorized users can't get the collection
        $client = static::createClient();

        $response = $client->request('GET', '/api/users');

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Unauthorized.',
        ]);
    }

    public function testCreateUser(): void
    {
        $client = static::createAdminClient();

        $client->request('POST', '/api/users', [
            'json' => [
                'username' => 'testuser',
                'roles' => ['ROLE_USER'],
                'plainPassword' => 'abcdef',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'User',
            'username' => 'testuser',
            'roles' => ['ROLE_USER'],
        ]);
        $this->assertMatchesResourceItemJsonSchema(User::class);

        // Test that the new user can login
        $client = static::createClient();

        $client->request('POST', '/api/login', [
            'headers' => ['content-type: application/json'],
            'json' => [
                'username' => 'testuser',
                'password' => 'abcdef',
            ],
        ]);

        $this->assertResponseIsSuccessful();

        // Test that regular users can't create a user
        $client = static::createRegularClient();
        $client->request('POST', '/api/users', [
            'json' => [
                'username' => 'testuser',
                'roles' => ['ROLE_USER'],
                'plainPassword' => 'abcdef',
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonContains([
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Access Denied.',
        ]);

        // Test that anonymous users can't create a user
        $client = static::createClient();
        $client->request('POST', '/api/users', [
            'json' => [
                'username' => 'testuser',
                'roles' => ['ROLE_USER'],
                'plainPassword' => 'abcdef',
            ],
        ]);
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Unauthorized.',
        ]);
    }

    public function testGetUser(): void
    {
        $client = static::createRegularClient();
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'johndoe'])
        ;

        $client->request('GET', "/api/users/{$user->getId()}");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@id' => "/api/users/{$user->getId()}",
            '@type' => 'User',
            'username' => 'johndoe',
            'roles' => ['ROLE_USER'],
        ]);
        $this->assertMatchesResourceItemJsonSchema(User::class);

        // Test that anonymous users can't get a user
        $client = static::createClient();
        $client->request('GET', "/api/users/{$user->getId()}");
        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Unauthorized.',
        ]);
    }

    public function testPutPatchUser(): void
    {
        $client = static::createAdminClient();

        // Set up a regular user for this test
        $client->request('POST', '/api/users', [
            'json' => [
                'username' => 'testuser',
                'roles' => ['ROLE_USER'],
                'plainPassword' => 'abcdef',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'User',
            'username' => 'testuser',
            'roles' => ['ROLE_USER'],
        ]);
        $this->assertMatchesResourceItemJsonSchema(User::class);

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'testuser'])
        ;

        // Test that an admin can replace a user resource
        $client->request('PUT', "/api/users/{$user->getId()}", [
            'json' => [
                'plainPassword' => '12345',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'User',
            'username' => 'testuser',
            'roles' => ['ROLE_USER'],
        ]);
        $this->assertMatchesResourceItemJsonSchema(User::class);

        // Test that a regular user cannot replace a user resource...
        $client = static::createRegularClient();

        $client->request('PUT', "/api/users/{$user->getId()}", [
            'json' => [
                'username' => 'testuser2',
                'roles' => ['ROLE_USER'],
                'plainPassword' => 'abcdef',
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonContains([
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Access Denied.',
        ]);

        // ... unless he's the user in question
        $johndoe = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'johndoe'])
        ;

        $client->request('PUT', "/api/users/{$johndoe->getId()}", [
            'json' => [
                'plainPassword' => 'abcdef',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'User',
        ]);
        $this->assertMatchesResourceItemJsonSchema(User::class);

        // Test that users can't grant themselves more privileges
        $client->request('PUT', "/api/users/{$johndoe->getId()}", [
            'json' => [
                'roles' => ['ROLE_USER', 'ROLE_EDIT_USERS'],
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'User',
            'username' => 'johndoe',
            'roles' => ['ROLE_USER'],
        ]);

        // Test that anonymous users can't replace a user resource
        $client = static::createClient();

        $client->request('PUT', "/api/users/{$johndoe->getId()}", [
            'json' => [
                'roles' => ['ROLE_USER', 'ROLE_EDIT_USERS'],
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'Unauthorized.',
        ]);
    }

    public function testCannotDeleteUsers(): void
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'johndoe'])
        ;

        // Test as admin
        $client = static::createAdminClient();
        $client->request('DELETE', "/api/users/{$user->getId()}");
        $this->assertResponseStatusCodeSame(405); // Method not allowed

        // Test as regular user
        $client = static::createRegularClient();
        $client->request('DELETE', "/api/users/{$user->getId()}");
        $this->assertResponseStatusCodeSame(405); // Method not allowed

        // Test as anonymous user
        $client = static::createClient();
        $client->request('DELETE', "/api/users/{$user->getId()}");
        $this->assertResponseStatusCodeSame(405); // Method not allowed
    }
}
