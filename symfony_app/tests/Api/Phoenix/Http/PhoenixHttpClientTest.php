<?php

declare(strict_types=1);

namespace App\Tests\Api\Phoenix\Http;

use App\Api\Phoenix\Http\PhoenixHttpClient;
use App\Exception\Phoenix\ApiConnectionException;
use App\Exception\Phoenix\ValidationException;
use App\Exception\Phoenix\UserNotFoundException;
use App\Exception\Phoenix\ServerErrorException;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class PhoenixHttpClientTest extends TestCase
{
    private function mockResponse(int $status, array $data): ResponseInterface
    {
        $mock = $this->createMock(ResponseInterface::class);

        $mock->method('getStatusCode')->willReturn($status);
        $mock->method('toArray')->willReturn($data);
        $mock->method('getContent')->willReturn(json_encode($data)); 
        return $mock;
    }

    /**
     * @return \Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface
     */
    private function mockHttpException(int $status, array $data): \Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface
    {
        return new class($this->mockResponse($status, $data)) extends \Exception implements \Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface {
            private $response;
            public function __construct($response) { $this->response = $response; }
            public function getResponse(): ResponseInterface { return $this->response; }
        };
    }

    public function testSuccessfulGet(): void
    {
        $http = $this->createMock(HttpClientInterface::class);

        $http->method('request')->willReturn(
            $this->mockResponse(200, ['id' => 1, 'name' => 'John'])
        );

        $client = new PhoenixHttpClient($http, 'http://test', 'token123');

        $result = $client->get('/users/1');

        $this->assertEquals(['id' => 1, 'name' => 'John'], $result);
    }

    public function testValidationExceptionIsThrown(): void
    {
        $http = $this->createMock(HttpClientInterface::class);

        $http->method('request')->willThrowException(
            $this->mockHttpException(422, [
                'error' => 'unprocessable_entity',
                'message' => 'Validation failed',
                'details' => ['first_name' => ["can't be blank"]]
            ])
        );

        $client = new PhoenixHttpClient($http, 'http://test', 'token123');

        $this->expectException(ValidationException::class);

        $client->post('/users', []);
    }

    public function testBadRequestExceptionIsThrown(): void
    {
        $http = $this->createMock(HttpClientInterface::class);

        $http->method('request')->willThrowException(
            $this->mockHttpException(400, [
                'error' => 'Bad Request',
                'message' => 'No update attributes provided'
            ])
        );

        $client = new PhoenixHttpClient($http, 'http://test', '');

        $this->expectException(ValidationException::class);

        $client->put('/users/1', []);
    }

    public function testUserNotFoundExceptionIsThrown(): void
    {
        $http = $this->createMock(HttpClientInterface::class);

        $http->method('request')->willThrowException(
            $this->mockHttpException(404, [
                'error' => 'not_found',
                'message' => 'User not found'
            ])
        );

        $client = new PhoenixHttpClient($http, 'http://test', '');

        $this->expectException(UserNotFoundException::class);

        $client->get('/users/999999');
    }

    public function testServerErrorExceptionIsThrown(): void
    {
        $http = $this->createMock(HttpClientInterface::class);

        $http->method('request')->willThrowException(
            $this->mockHttpException(500, [
                'error' => 'Internal Server Error',
                'message' => 'An unexpected error occurred'
            ])
        );

        $client = new PhoenixHttpClient($http, 'http://test', '');

        $this->expectException(ServerErrorException::class);

        $client->get('/users/1');
    }

    public function testTransportExceptionBecomesApiConnectionException(): void
    {
        $http = $this->createMock(HttpClientInterface::class);

        $http->method('request')->willThrowException(
            new class extends \Exception implements TransportExceptionInterface {}
        );

        $client = new PhoenixHttpClient($http, 'http://test', 'token');

        $this->expectException(ApiConnectionException::class);

        $client->get('/users');
    }
}
