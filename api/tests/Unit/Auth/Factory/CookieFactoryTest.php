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

namespace App\Tests\Unit\Auth\Factory;

use App\Auth\Factory\CookieFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;

final class CookieFactoryTest extends TestCase
{
    public function testCreateCookieCreatesHttpOnlyCookie(): void
    {
        $factory = new CookieFactory(cookieSecure: false);

        $cookie = $factory->createCookie(
            CookieFactory::ACCESS_TOKEN_COOKIE,
            'access-token-value',
            900,
        );

        self::assertSame(CookieFactory::ACCESS_TOKEN_COOKIE, $cookie->getName());
        self::assertSame('access-token-value', $cookie->getValue());
        self::assertTrue($cookie->isHttpOnly());
    }

    public function testCreateCookieUsesRootPath(): void
    {
        $factory = new CookieFactory(cookieSecure: false);

        $cookie = $factory->createCookie(
            CookieFactory::ACCESS_TOKEN_COOKIE,
            'access-token-value',
            900,
        );

        self::assertSame('/', $cookie->getPath());
    }

    public function testCreateCookieUsesSameSiteLax(): void
    {
        $factory = new CookieFactory(cookieSecure: false);

        $cookie = $factory->createCookie(
            CookieFactory::ACCESS_TOKEN_COOKIE,
            'access-token-value',
            900,
        );

        self::assertSame(Cookie::SAMESITE_LAX, $cookie->getSameSite());
    }

    public function testCreateCookieCanCreateSecureCookie(): void
    {
        $factory = new CookieFactory(cookieSecure: true);

        $cookie = $factory->createCookie(
            CookieFactory::ACCESS_TOKEN_COOKIE,
            'access-token-value',
            900,
        );

        self::assertTrue($cookie->isSecure());
    }

    public function testCreateCookieCanCreateNonSecureCookie(): void
    {
        $factory = new CookieFactory(cookieSecure: false);

        $cookie = $factory->createCookie(
            CookieFactory::ACCESS_TOKEN_COOKIE,
            'access-token-value',
            900,
        );

        self::assertFalse($cookie->isSecure());
    }

    public function testCreateCookieSetsExpirationBasedOnTtl(): void
    {
        $factory = new CookieFactory(cookieSecure: false);

        $before = time();

        $cookie = $factory->createCookie(
            CookieFactory::ACCESS_TOKEN_COOKIE,
            'access-token-value',
            900,
        );

        $after = time();

        self::assertGreaterThanOrEqual($before + 900, $cookie->getExpiresTime());
        self::assertLessThanOrEqual($after + 900, $cookie->getExpiresTime());
    }

    public function testExpireCookieCreatesExpiredHttpOnlyCookie(): void
    {
        $factory = new CookieFactory(cookieSecure: false);

        $cookie = $factory->expireCookie(CookieFactory::ACCESS_TOKEN_COOKIE);

        self::assertSame(CookieFactory::ACCESS_TOKEN_COOKIE, $cookie->getName());
        self::assertSame('', $cookie->getValue());
        self::assertTrue($cookie->isHttpOnly());
        self::assertLessThanOrEqual(time(), $cookie->getExpiresTime());
    }

    public function testExpireCookieUsesRootPathAndSameSiteLax(): void
    {
        $factory = new CookieFactory(cookieSecure: false);

        $cookie = $factory->expireCookie(CookieFactory::REFRESH_TOKEN_COOKIE);

        self::assertSame('/', $cookie->getPath());
        self::assertSame(Cookie::SAMESITE_LAX, $cookie->getSameSite());
    }

    public function testExpireCookieRespectsSecureFlag(): void
    {
        $factory = new CookieFactory(cookieSecure: true);

        $cookie = $factory->expireCookie(CookieFactory::REFRESH_TOKEN_COOKIE);

        self::assertTrue($cookie->isSecure());
    }
}
