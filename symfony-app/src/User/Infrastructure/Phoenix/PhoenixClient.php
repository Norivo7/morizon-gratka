<?php
declare(strict_types=1);

namespace App\User\Infrastructure\Phoenix;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PhoenixClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
    ) {}

    private function request(string $method, string $path, array $options = []): array
    {
        $response = $this->httpClient->request(
            $method,
            rtrim($this->baseUrl, '/').$path,
            $options
        );

        $status = $response->getStatusCode();

        if ($status >= 400) {
            $body = $response->getContent(false);
            throw new \RuntimeException("Phoenix API error {$status}: {$body}");
        }

        if ($status === Response::HTTP_NO_CONTENT) {
            return [];
        }

        return $response->toArray(false);
    }

    public function listUsers(array $query = []): array
    {
        $payload = $this->request('GET', '/api/users', ['query' => $query]);
        return $payload['data'] ?? [];
    }

    public function getUser(int $id): array
    {
        $payload = $this->request('GET', "/api/users/{$id}");
        return $payload['data'] ?? [];
    }

    public function createUser(array $data): array
    {
        $payload = $this->request('POST', '/api/users', ['json' => $data]);
        return $payload['data'] ?? [];
    }

    public function updateUser(int $id, array $data): array
    {
        $payload = $this->request('PUT', "/api/users/{$id}", ['json' => $data]);
        return $payload['data'] ?? [];
    }

    public function deleteUser(int $id): void
    {
        $this->request('DELETE', "/api/users/{$id}");
    }
}