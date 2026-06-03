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

use App\Wallet\Enum\CurrencyCode;
use App\Wallet\Enum\WalletType;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateWalletRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,

        #[Assert\NotNull]
        public WalletType $type,

        #[Assert\NotNull]
        public CurrencyCode $currency,

        #[Assert\PositiveOrZero]
        public int $balanceAmount = 0,
    ) {
    }
}
