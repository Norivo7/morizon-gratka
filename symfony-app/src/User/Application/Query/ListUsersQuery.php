<?php

declare(strict_types=1);

namespace App\User\Application\Query;

final readonly class ListUsersQuery
{
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $gender = null,
        public ?string $birthdateFrom = null,
        public ?string $birthdateTo = null,
        public ?string $sortBy = null,
        public ?string $sortDir = null,
    ) {
    }
}
