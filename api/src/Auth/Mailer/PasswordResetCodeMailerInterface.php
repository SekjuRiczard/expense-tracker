<?php

declare(strict_types=1);

namespace App\Auth\Mailer;

use App\Entity\User;

interface PasswordResetCodeMailerInterface
{
    public function sendPasswordResetCode(User $user, string $code): void;
}