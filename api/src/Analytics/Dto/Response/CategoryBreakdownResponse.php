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

namespace App\Analytics\Dto\Response;

final readonly class CategoryBreakdownResponse
{
    public function __construct(
        public int $categoryId,
        public string $categoryName,
        public int $amount,
        public float $percentage,
    ) {
    }
}