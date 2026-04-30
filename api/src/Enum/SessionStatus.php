<?php

declare(strict_types=1);

namespace App\Enum;

enum SessionStatus: string
{
    case PIN_SETUP_REQUIRED = 'pin_setup_required';
    case PIN_VERIFICATION_REQUIRED = 'pin_verification_required';
    case AUTHENTICATED = 'authenticated';
    case REVOKED = 'revoked';
    case EXPIRED = 'expired';

    public function isPartial(): bool
    {
        return in_array($this, [
            self::PIN_SETUP_REQUIRED,
            self::PIN_VERIFICATION_REQUIRED,
        ], true);
    }

    public function isAuthenticated(): bool
    {
        return $this === self::AUTHENTICATED;
    }
}