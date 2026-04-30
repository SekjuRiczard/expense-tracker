<?php

declare(strict_types=1);

namespace App\Enum;

enum AuthStage: string
{
    case PIN_SETUP_REQUIRED = 'pin_setup_required';
    case PIN_VERIFICATION_REQUIRED = 'pin_verification_required';
    case AUTHENTICATED = 'authenticated';

    public static function fromSessionStatus(SessionStatus $status): self
    {
        return match ($status) {
            SessionStatus::PIN_SETUP_REQUIRED => self::PIN_SETUP_REQUIRED,
            SessionStatus::PIN_VERIFICATION_REQUIRED => self::PIN_VERIFICATION_REQUIRED,
            SessionStatus::AUTHENTICATED => self::AUTHENTICATED,
            default => throw new \InvalidArgumentException('Unsupported session status for auth stage.'),
        };
    }
}