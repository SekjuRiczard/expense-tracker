<?php

declare(strict_types=1);

namespace App\Service\Contract;

use App\Entity\User;

interface AuthenticatedUserResolverInterface
{
    public function resolve(?User $user): User;
}
