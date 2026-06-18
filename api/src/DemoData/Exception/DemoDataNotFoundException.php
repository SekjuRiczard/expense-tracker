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

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DemoDataNotFoundException extends NotFoundHttpException
{
    public static function noActiveBatch(): self
    {
        return new self(
            'There is no demo data to clear.',
        );
    }
}
