<?php

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