<?php
declare(strict_types=1);

namespace App\User\Infrastructure\Phoenix;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PhoenixClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
    ) {}

    public function listUsers(array $query = []): array
    {
        $response = $this->httpClient->request(
            'GET',
            rtrim($this->baseUrl, '/').'/api/users',
            ['query' => $query]
        );

        $payload = $response->toArray(false);

        return $payload['data'] ?? [];
    }
}