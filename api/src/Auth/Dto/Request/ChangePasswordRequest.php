<?php

declare(strict_types=1);

namespace App\Auth\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangePasswordRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $oldPassword,

        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        public ?string $newPassword,

        #[Assert\NotBlank]
        public ?string $confirmNewPassword,
    ) {
    }
}