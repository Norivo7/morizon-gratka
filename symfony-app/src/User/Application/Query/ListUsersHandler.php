<?php

declare(strict_types=1);

namespace App\User\Application\Query;

use App\User\Infrastructure\Phoenix\PhoenixClient;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListUsersHandler
{
    public function __construct(private PhoenixClient $client)
    {
    }

    public function __invoke(ListUsersQuery $q): array
    {
        $params = [];

        if (null !== $q->firstName && '' !== $q->firstName) {
            $params['first_name'] = $q->firstName;
        }
        if (null !== $q->lastName && '' !== $q->lastName) {
            $params['last_name'] = $q->lastName;
        }
        if (null !== $q->gender && '' !== $q->gender) {
            $params['gender'] = $q->gender;
        }
        if (null !== $q->birthdateFrom && '' !== $q->birthdateFrom) {
            $params['birthdate_from'] = $q->birthdateFrom;
        }
        if (null !== $q->birthdateTo && '' !== $q->birthdateTo) {
            $params['birthdate_to'] = $q->birthdateTo;
        }
        if (null !== $q->sortBy && '' !== $q->sortBy) {
            $params['sort_by'] = $q->sortBy;
        }
        if (null !== $q->sortDir && '' !== $q->sortDir) {
            $params['sort_dir'] = $q->sortDir;
        }

        return $this->client->listUsers($params);
    }
}
