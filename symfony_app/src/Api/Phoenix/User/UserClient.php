<?php

declare(strict_types=1);

namespace App\Api\Phoenix\User;

use App\Api\Phoenix\Http\PhoenixHttpClient;
use App\Exception\Phoenix\ApiResponseException;
use InvalidArgumentException;

class UserClient implements UserClientInterface
{
    private readonly PhoenixHttpClient $client;

    public function __construct(PhoenixHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Downloading a list of users with filters and sorting.
     *
     * @param array<string,mixed> $filters
     * @return array<string,mixed>
     */
    public function listUsers(array $filters = []): array
    {
        $response = $this->client->get('/users', $filters);

        if (!\is_array($response)) {
            throw new ApiResponseException('Invalid API response for user list.');
        }

        return $response;
    }

    /**
     * User details.
     *
     * @param int $id
     * @return array<string,mixed>
     */
    public function getUser(int $id): array
    {
        $response = $this->client->get("/users/{$id}");

        if (!\is_array($response)) {
            throw new ApiResponseException("Invalid API response for user #{$id}.");
        }

        if (isset($response['birthdate']) && \is_string($response['birthdate'])) {
            $response['birthdate'] = \DateTime::createFromFormat('Y-m-d', $response['birthdate']) ?: null;
        }

        return $response;
    }

    /**
     * Creating a user.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function createUser(array $payload): array
    {
        if (isset($payload['birthdate']) && $payload['birthdate'] instanceof \DateTimeInterface) {
            $payload['birthdate'] = $payload['birthdate']->format('Y-m-d');
        }
        $this->validateUserData($payload);
        return $this->client->post('/users', $payload);
    }

    /**
     * Updating a user.
     *
     * @param int $id
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function updateUser(int $id, array $payload): array
    {
        if (isset($payload['birthdate']) && $payload['birthdate'] instanceof \DateTimeInterface) {
            $payload['birthdate'] = $payload['birthdate']->format('Y-m-d');
        }
        $this->validateUserData($payload);
        return $this->client->put("/users/{$id}", $payload);
    }

    /**
     * Deleting a user.
     *
     * @param int $id
     */
    public function deleteUser(int $id): void
    {
        try {
            $this->client->delete("/users/{$id}");
        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            throw new ApiResponseException($e->getMessage());
        } catch (\Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface $e) {
            $status = $e->getResponse()?->getStatusCode();
            throw new ApiResponseException(
                "Phoenix API returned HTTP {$status}: " . $e->getMessage(),
                $status,
                $e
            );
        }
    }

    private function validateUserData(array $data): void
    {
        if (
            !isset($data['first_name']) ||
            !\is_string($data['first_name']) ||
            trim($data['first_name']) === ''
        ) {
            throw new InvalidArgumentException('First name is required and must be a non-empty string.');
        }
        if (preg_match('/<[^>]+>/', $data['first_name'])) {
            throw new InvalidArgumentException('First name contains invalid characters.');
        }
    }
}
