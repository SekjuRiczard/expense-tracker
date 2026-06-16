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

namespace App\DemoData\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class DemoDataException extends ConflictHttpException
{
    public static function alreadyGenerated(): self
    {
        return new self(
            'Demo data has already been generated.',
        );
    }
}
