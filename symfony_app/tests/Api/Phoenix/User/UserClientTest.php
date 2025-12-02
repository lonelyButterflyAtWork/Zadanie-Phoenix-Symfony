<?php

declare(strict_types=1);

namespace App\Tests\Api\Phoenix\User;

use App\Api\Phoenix\User\UserClient;
use App\Api\Phoenix\Http\PhoenixHttpClient;
use PHPUnit\Framework\TestCase;

class UserClientTest extends TestCase
{
    public function testListUsersReturnsArray(): void
    {
        $http = $this->createMock(PhoenixHttpClient::class);
        $http->method('get')->willReturn([
            ['id' => 1, 'first_name' => 'Jan'],
            ['id' => 2, 'first_name' => 'Anna'],
        ]);

        $client = new UserClient($http);
        $result = $client->listUsers();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Jan', $result[0]['first_name']);
    }

    public function testGetUserReturnsUserArray(): void
    {
        $http = $this->createMock(PhoenixHttpClient::class);
        $http->method('get')->willReturn([
            'id' => 1,
            'first_name' => 'Jan',
            'birthdate' => '2000-01-01'
        ]);

        $client = new UserClient($http);
        $result = $client->getUser(1);

        $this->assertIsArray($result);
        $this->assertEquals('Jan', $result['first_name']);
        $this->assertInstanceOf(\DateTimeInterface::class, $result['birthdate']);
    }

    public function testCreateUserReturnsCreatedUser(): void
    {
        $http = $this->createMock(PhoenixHttpClient::class);
        $http->method('post')->willReturn(['id' => 3, 'first_name' => 'Adam']);

        $client = new UserClient($http);
        $result = $client->createUser(['first_name' => 'Adam']);

        $this->assertIsArray($result);
        $this->assertEquals(3, $result['id']);
    }

    public function testUpdateUserReturnsUpdatedUser(): void
    {
        $http = $this->createMock(PhoenixHttpClient::class);
        $http->method('put')->willReturn(['id' => 1, 'first_name' => 'Janek']);

        $client = new UserClient($http);
        $result = $client->updateUser(1, ['first_name' => 'Janek']);

        $this->assertIsArray($result);
        $this->assertEquals('Janek', $result['first_name']);
    }

    public function testDeleteUserDoesNotThrow(): void
    {
        $http = $this->createMock(PhoenixHttpClient::class);
        $http->method('delete')->willReturn([]);

        $client = new UserClient($http);

        $this->expectNotToPerformAssertions();
        $client->deleteUser(1);
    }
}
