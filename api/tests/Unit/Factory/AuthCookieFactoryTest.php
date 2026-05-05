<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\AuthCookieFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;

final class AuthCookieFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_http_only_access_token_cookie(): void
    {
        $factory = new AuthCookieFactory(secure: true);

        $cookie = $factory->createAccessTokenCookie('access-token');

        self::assertSame('access_token', $cookie->getName());
        self::assertSame('access-token', $cookie->getValue());
        self::assertSame('/', $cookie->getPath());
        self::assertTrue($cookie->isHttpOnly());
        self::assertTrue($cookie->isSecure());
        self::assertSame(Cookie::SAMESITE_LAX, $cookie->getSameSite());
    }

    #[Test]
    public function it_creates_http_only_refresh_token_cookie(): void
    {
        $factory = new AuthCookieFactory(secure: true);

        $cookie = $factory->createRefreshTokenCookie('refresh-token');

        self::assertSame('refresh_token', $cookie->getName());
        self::assertSame('refresh-token', $cookie->getValue());
        self::assertSame('/api/token', $cookie->getPath());
        self::assertTrue($cookie->isHttpOnly());
        self::assertTrue($cookie->isSecure());
        self::assertSame(Cookie::SAMESITE_LAX, $cookie->getSameSite());
    }

    #[Test]
    public function it_creates_expired_access_token_cookie(): void
    {
        $factory = new AuthCookieFactory(secure: true);

        $cookie = $factory->createExpiredAccessTokenCookie();

        self::assertSame('access_token', $cookie->getName());
        self::assertSame('', $cookie->getValue());
        self::assertSame('/', $cookie->getPath());
        self::assertTrue($cookie->isHttpOnly());
        self::assertTrue($cookie->isSecure());
        self::assertLessThan(time(), $cookie->getExpiresTime());
    }

    #[Test]
    public function it_creates_expired_refresh_token_cookie(): void
    {
        $factory = new AuthCookieFactory(secure: true);

        $cookie = $factory->createExpiredRefreshTokenCookie();

        self::assertSame('refresh_token', $cookie->getName());
        self::assertSame('', $cookie->getValue());
        self::assertSame('/api/token', $cookie->getPath());
        self::assertTrue($cookie->isHttpOnly());
        self::assertTrue($cookie->isSecure());
        self::assertLessThan(time(), $cookie->getExpiresTime());
    }
}
