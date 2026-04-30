<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Session;
use App\Enum\AuthStage;

final readonly class AuthTokenResponse
{
    public function __construct(
        public string $token,
        public AuthStage $authStage,
        public Session $session,
    ) {
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'status' => $this->authStage->value,
        ];
    }
}