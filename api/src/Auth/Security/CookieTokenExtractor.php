<?php

/**
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Auth\Security;

use App\Auth\Factory\CookieFactory;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

final class CookieTokenExtractor implements TokenExtractorInterface
{
    public function extract(Request $request): string|false
    {
        if (is_string($request->cookies->get(CookieFactory::ACCESS_TOKEN_COOKIE))
            && '' !== $request->cookies->get(CookieFactory::ACCESS_TOKEN_COOKIE)
        ) {
            return $request->cookies->get(CookieFactory::ACCESS_TOKEN_COOKIE);
        }
        if (
            is_string($request->cookies->get(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE))
            && '' !== $request->cookies->get(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE)
        ) {
            return $request->cookies->get(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        }

        return false;
    }
}
