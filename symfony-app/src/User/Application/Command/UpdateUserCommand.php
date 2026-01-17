<?php

declare(strict_types=1);

namespace App\User\Application\Command;

final readonly class UpdateUserCommand
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $gender,
        public string $birthdate,
    ) {
    }
}
