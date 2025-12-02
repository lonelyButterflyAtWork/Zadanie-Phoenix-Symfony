<?php

declare(strict_types=1);

namespace App\Api\Phoenix\User;

interface UserClientInterface
{
    public function listUsers(array $filters = []): array;

    public function getUser(int $id): array;

    public function createUser(array $payload): array;

    public function updateUser(int $id, array $payload): array;

    public function deleteUser(int $id): void;
}
