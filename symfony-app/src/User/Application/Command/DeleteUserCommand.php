<?php

declare(strict_types=1);

namespace App\User\Application\Command;

final readonly class DeleteUserCommand
{
    public function __construct(public int $id)
    {
    }
}
