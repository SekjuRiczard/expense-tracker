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

use App\DemoData\Entity\DemoDataBatch;

final readonly class DemoDataStatusResponse
{
    public function __construct(
        public bool $demoDataExists,
        public ?string $generatedAt,
        public int $walletsCount,
        public int $budgetsCount,
        public int $transactionsCount,
    ) {
    }

    public static function fromBatch(?DemoDataBatch $batch): self
    {
        if (null === $batch) {
            return new self(
                demoDataExists: false,
                generatedAt: null,
                walletsCount: 0,
                budgetsCount: 0,
                transactionsCount: 0,
            );
        }

        return new self(
            demoDataExists: true,
            generatedAt: $batch->getGeneratedAt()->format(DATE_ATOM),
            walletsCount: $batch->getWalletsCount(),
            budgetsCount: $batch->getBudgetsCount(),
            transactionsCount: $batch->getTransactionsCount(),
        );
    }
}
