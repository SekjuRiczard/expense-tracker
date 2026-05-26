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

namespace App\Auth\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResetPasswordRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public ?string $email,

        #[Assert\NotBlank]
        #[Assert\Length(exactly: 6)]
        #[Assert\Regex(pattern: '/^\d{6}$/')]
        public ?string $code,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public ?string $newPassword,

        #[Assert\NotBlank]
        public ?string $confirmNewPassword,
    ) {
    }
}
