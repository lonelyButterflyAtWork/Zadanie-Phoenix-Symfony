<?php

declare(strict_types=1);

namespace App\Api\Phoenix\Http;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Exception\Phoenix\ApiException;
use App\Exception\Phoenix\ApiConnectionException;
use App\Exception\Phoenix\ErrorMapper;

class PhoenixHttpClient
{
    private string $baseUrl;

    public function __construct(
        private readonly HttpClientInterface $client,
        string $baseUrl,
        private readonly string $token
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * @param array<string,mixed> $query
     * @return array<string,mixed>
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * @param array<string,mixed> $json
     * @return array<string,mixed>
     */
    public function post(string $endpoint, array $json = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $json]);
    }

    /**
     * @param array<string,mixed> $json
     * @return array<string,mixed>
     */
    public function put(string $endpoint, array $json = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $json]);
    }

    /**
     * @return array<string,mixed>
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * @param array<string,mixed> $options
     * @return array<string,mixed>
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['headers']['Accept'] = 'application/json';

        try {
            $response = $this->client->request($method, $url, $options);
            $status = $response->getStatusCode();

            if (\in_array($status, [200, 204], true) && $this->isEmptyResponse($response)) {
                return [];
            }

            $data = $response->toArray(false);

            if ($status >= 400) {
                throw ErrorMapper::map($status, $data);
            }
        } catch (TransportExceptionInterface $e) {
            throw new ApiConnectionException(
                \sprintf('Phoenix API connection error: %s', $e->getMessage()),
                previous: $e
            );
        } catch (HttpExceptionInterface $e) {
            $response = $e->getResponse();
            $status = $response->getStatusCode();
            if (\in_array($status, [200, 204], true) && $this->isEmptyResponse($response)) {
                return [];
            }
            $data = $response->toArray(false);
            throw ErrorMapper::map($status, $data);
        } catch (\Throwable $e) {
            throw new ApiException(
                \sprintf('Unexpected Phoenix API error: %s', $e->getMessage()),
                previous: $e
            );
        }

        return $this->normalizeData($data);
    }

    /**
     * @param mixed $data
     * @return array<string,mixed>
     */
    private function normalizeData(mixed $data): array
    {
        return (\is_array($data) && \array_key_exists('data', $data)) ? $data['data'] : (array) $data;
    }

    /**
     * @param mixed $response
     * @return bool
     */
    private function isEmptyResponse($response): bool
    {
        $content = $response->getContent(false);
        return $content === '' || $content === null;
    }
}
