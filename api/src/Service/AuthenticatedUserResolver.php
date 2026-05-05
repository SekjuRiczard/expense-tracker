<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Exception\UnauthenticatedUserException;
use App\Service\Contract\AuthenticatedUserResolverInterface;

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
