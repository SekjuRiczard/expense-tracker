<?php

declare(strict_types=1);

namespace App\Auth\Mailer;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SymfonyPasswordResetCodeMailer implements PasswordResetCodeMailerInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromEmail,
    ) {
    }

    public function sendPasswordResetCode(User $user, string $code): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject('Password reset code')
            ->text(sprintf(
                "Your password reset code is: %s\n\nThis code expires in 15 minutes.",
                $code,
            ));

        $this->mailer->send($email);
    }
}