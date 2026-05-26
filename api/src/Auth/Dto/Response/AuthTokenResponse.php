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

namespace App\Auth\Dto\Response;

use App\Entity\User;
use App\Enum\ResponseMessage;
use App\Enum\SessionStatus;

readonly class AuthTokenResponse
{
    public function __construct(
        public SessionStatus $status,
        public User $user,
        public ResponseMessage $message,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'message' => $this->message->t(),
            'user' => [
                'id' => (string) $this->user->getId(),
                'email' => $this->user->getEmail(),
                'username' => $this->user->getUsername(),
                'hasPin' => null !== $this->user->getPin(),
            ],
        ];
    }
}
