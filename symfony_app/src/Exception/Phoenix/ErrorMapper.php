<?php

declare(strict_types=1);

namespace App\Exception\Phoenix;

class ErrorMapper
{
    /**
     * @param array<string,mixed> $payload
     */
    public static function map(int $status, array $payload): ApiException
    {
        $message = $payload['message'] ?? '';

        return match ($status) {
            400, 422 => new ValidationException(
                $message ?: ($status === 400 ? 'Bad Request' : 'Validation failed'),
                $status
            ),
            401 => new UnauthorizedException('Unauthorized', 401),
            404 => new UserNotFoundException($message ?: 'User not found', 404),
            500 => new ServerErrorException($message ?: 'Phoenix internal server error', 500),
            default => new ApiResponseException(
                \sprintf('Phoenix API returned HTTP %d: %s', $status, json_encode($payload)),
                $status
            )
        };
    }
}
