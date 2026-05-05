<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token;

use App\Service\Token\Refresh\CookieRefreshTokenResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class CookieRefreshTokenResolverTest extends TestCase
{
    #[Test]
    public function it_resolves_refresh_token_from_cookie(): void
    {
        $request = Request::create('/api/token/refresh', 'POST');
        $request->cookies->set('refresh_token', 'refresh-token');

        self::assertSame('refresh-token', (new CookieRefreshTokenResolver())->resolve($request));
    }

    #[Test]
    public function it_supports_custom_cookie_name(): void
    {
        $request = Request::create('/api/token/refresh', 'POST');
        $request->cookies->set('custom_refresh_token', 'refresh-token');

        self::assertSame('refresh-token', (new CookieRefreshTokenResolver('custom_refresh_token'))->resolve($request));
    }

    #[Test]
    public function it_returns_null_when_cookie_is_missing(): void
    {
        $request = Request::create('/api/token/refresh', 'POST');

        self::assertNull((new CookieRefreshTokenResolver())->resolve($request));
    }

    #[Test]
    public function it_returns_null_when_cookie_is_blank(): void
    {
        $request = Request::create('/api/token/refresh', 'POST');
        $request->cookies->set('refresh_token', '   ');

        self::assertNull((new CookieRefreshTokenResolver())->resolve($request));
    }
}
