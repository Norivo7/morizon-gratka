<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\User\Infrastructure\Phoenix\PhoenixClient;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateUserHandler
{
    public function __construct(private PhoenixClient $client)
    {
    }

    public function __invoke(UpdateUserCommand $command): array
    {
        return $this->client->updateUser($command->id, [
            'user' => [
                'first_name' => $command->firstName,
                'last_name' => $command->lastName,
                'gender' => $command->gender,
                'birthdate' => $command->birthdate,
            ],
        ]);
    }
}
