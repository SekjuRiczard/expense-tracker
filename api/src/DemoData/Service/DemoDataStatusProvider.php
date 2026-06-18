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

namespace App\DemoData\Service;

use App\DemoData\Dto\Response\DemoDataStatusResponse;
use App\DemoData\Repository\DemoDataBatchRepository;
use App\Entity\User;

final readonly class DemoDataStatusProvider
{
    public function __construct(
        private DemoDataBatchRepository $demoDataBatchRepository,
    ) {
    }

    public function getStatus(User $user): DemoDataStatusResponse
    {
        return DemoDataStatusResponse::fromBatch(
            $this->demoDataBatchRepository->findActiveByUser($user),
        );
    }
}
