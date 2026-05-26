<?php

/**
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Auth\Factory;

use Symfony\Component\HttpFoundation\Cookie;

class CookieFactory
{
    public const ACCESS_TOKEN_COOKIE = 'access_token';
    public const PARTIAL_ACCESS_TOKEN_COOKIE = 'partial_access_token';
    public const REFRESH_TOKEN_COOKIE = 'refresh_token';

    public function __construct(private bool $cookieSecure)
    {
    }

    public function createCookie(string $name, string $value, int $ttl): Cookie
    {
        return new Cookie(
            $name,
            $value,
            time() + $ttl,
            '/',
            null,
            $this->cookieSecure,
            true,
            false,
            Cookie::SAMESITE_LAX
        );
    }

    public function expireCookie(string $name): Cookie
    {
        return new Cookie($name, '', 1, '/', null, $this->cookieSecure, true, false, Cookie::SAMESITE_LAX);
    }
}
