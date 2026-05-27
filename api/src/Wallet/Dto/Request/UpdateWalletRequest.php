<?php

/*
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Wallet\Dto\Request;

use App\Wallet\Enum\WalletType;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateWalletRequest
{
    public function __construct(
        #[Assert\Length(min: 1, max: 255)]
        public ?string $name = null,

        public ?WalletType $type = null,
    ) {
    }
}