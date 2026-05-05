<?php

declare(strict_types=1);

namespace App\Auth\Service;

use App\Auth\Service\Contract\AuthenticatedUserResolverInterface;
use App\Entity\User;
use App\Shared\Exception\UnauthenticatedUserException;

final readonly class AuthenticatedUserResolver implements AuthenticatedUserResolverInterface
{
    public function resolve(?User $user): User
    {
        if ($user instanceof User) {
            return $user;
        }

        throw new UnauthenticatedUserException();
    }
}
