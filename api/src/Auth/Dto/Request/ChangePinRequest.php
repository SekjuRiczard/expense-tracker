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

class ChangePinRequest
{
    #[Assert\NotBlank]
    public string $oldPin;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    #[Assert\Regex(pattern: '/^\d+$/', message: 'The PIN must contain digits only.')]
    public string $newPin;
}
