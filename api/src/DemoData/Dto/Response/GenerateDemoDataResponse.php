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

namespace App\DemoData\Dto\Response;

final readonly class GenerateDemoDataResponse
{
    public function __construct(
        public int $seed,
        public int $monthsGenerated,
        public int $defaultCategoriesCreated,
        public int $walletsCreated,
        public int $budgetsCreated,
        public int $transactionsCreated,
        public bool $demoDataExists = true,
    ) {
    }
}
