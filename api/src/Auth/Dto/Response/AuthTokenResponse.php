<?php

/*
 * This file is part of the Expense Tracker.
 *
 * (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Auth\Dto\Response;

use App\Entity\Session;
use App\Enum\AuthStage;

final readonly class AuthTokenResponse
{
    public function __construct(
        public string $token,
        public AuthStage $authStage,
        public Session $session,
        public ?string $refreshToken = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'token' => $this->token,
            'status' => $this->authStage->value,
        ];

        if ($this->refreshToken !== null) {
            $data['refreshToken'] = $this->refreshToken;
        }

        return $data;
    }
}