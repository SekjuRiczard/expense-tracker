<?php

declare(strict_types=1);

namespace App\Session\Service;

use App\Entity\Session;
use App\Enum\SessionStatus;
use App\Session\Service\Contract\SessionStatusGuardInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class SessionStatusGuard implements SessionStatusGuardInterface
{
    public function ensureStatus(Session $session, SessionStatus $expectedStatus): void
    {
        if ($session->getStatus() === $expectedStatus) {
            return;
        }

        throw new AccessDeniedHttpException(sprintf(
            'Invalid session status. Expected "%s", got "%s".',
            $expectedStatus->value,
            $session->getStatus()->value,
        ));
    }
}
