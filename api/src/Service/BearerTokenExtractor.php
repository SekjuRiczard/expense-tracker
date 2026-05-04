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

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class BearerTokenExtractor
{
    public function extract(Request $request): string
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if ($authorizationHeader === null) {
            throw new UnauthorizedHttpException('Bearer', 'Missing Authorization header.');
        }

        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid Authorization header.');
        }

        $token = trim(substr($authorizationHeader, 7));

        if ($token === '') {
            throw new UnauthorizedHttpException('Bearer', 'Missing bearer token.');
        }

        return $token;
    }
}