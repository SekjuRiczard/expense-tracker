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

namespace App\Wallet\Enum;

enum WalletType: string
{
    case CASH = 'cash';
    case BANK_ACCOUNT = 'bank_account';
    case CREDIT_CARD = 'credit_card';
    case SAVINGS_ACCOUNT = 'savings_account';
}