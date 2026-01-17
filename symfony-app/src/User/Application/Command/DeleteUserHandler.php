<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\User\Infrastructure\Phoenix\PhoenixClient;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteUserHandler
{
    public function __construct(private PhoenixClient $client)
    {
    }

    public function __invoke(DeleteUserCommand $command): void
    {
        $this->client->deleteUser($command->id);
    }
}
