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

namespace App\Budget\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class BudgetAlreadyExistsException extends ConflictHttpException
{
    public function __construct()
    {
        parent::__construct(
            'A budget for the selected period and currency already exists.',
        );
    }
}