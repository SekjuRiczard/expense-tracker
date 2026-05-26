<?php

/**
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Auth\Mailer;

use App\Entity\User;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SymfonyPasswordResetCodeMailer implements PasswordResetCodeMailerInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromEmail,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetCode(User $user, string $code): void
    {
        /** @var Email $email */
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
