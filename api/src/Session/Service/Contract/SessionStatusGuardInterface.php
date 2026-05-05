<?php

declare(strict_types=1);

namespace App\Session\Service\Contract;

use App\Entity\Session;
use App\Enum\SessionStatus;

interface SessionStatusGuardInterface
{
    public function ensureStatus(Session $session, SessionStatus $expectedStatus): void;
}
