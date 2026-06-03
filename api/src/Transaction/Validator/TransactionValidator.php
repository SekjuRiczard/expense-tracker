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

namespace App\Transaction\Validator;

use App\Category\Entity\Category;
use App\Transaction\Enum\TransactionType;
use App\Transaction\Exception\TransactionException;

final readonly class TransactionValidator
{
    public function validateCategoryType(
        Category $category,
        TransactionType $transactionType,
    ): void {
        if ($category->getType()->value !== $transactionType->value) {
            throw TransactionException::categoryTypeMismatch();
        }
    }
}
