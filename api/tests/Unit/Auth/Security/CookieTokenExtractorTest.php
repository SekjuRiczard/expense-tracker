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

namespace App\Tests\Unit\Auth\Security;

use App\Auth\Factory\CookieFactory;
use App\Auth\Security\CookieTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class CookieTokenExtractorTest extends TestCase
{
    private CookieTokenExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new CookieTokenExtractor();
    }

    public function testExtractReturnsAccessTokenWhenAccessTokenCookieExists(): void
    {
        $request = Request::create('/api/me');
        $request->cookies->set(CookieFactory::ACCESS_TOKEN_COOKIE, 'access-token-value');

        self::assertSame('access-token-value', $this->extractor->extract($request));
    }

    public function testExtractReturnsPartialAccessTokenWhenAccessTokenCookieIsMissing(): void
    {
        $request = Request::create('/api/pin/setup');
        $request->cookies->set(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE, 'partial-access-token-value');

        self::assertSame('partial-access-token-value', $this->extractor->extract($request));
    }

    public function testExtractPrefersAccessTokenWhenBothCookiesExist(): void
    {
        $request = Request::create('/api/me');
        $request->cookies->set(CookieFactory::ACCESS_TOKEN_COOKIE, 'access-token-value');
        $request->cookies->set(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE, 'partial-access-token-value');

        self::assertSame('access-token-value', $this->extractor->extract($request));
    }

    public function testExtractReturnsFalseWhenNoTokenCookieExists(): void
    {
        $request = Request::create('/api/me');

        self::assertFalse($this->extractor->extract($request));
    }

    public function testExtractIgnoresEmptyAccessTokenAndReturnsPartialToken(): void
    {
        $request = Request::create('/api/pin/setup');
        $request->cookies->set(CookieFactory::ACCESS_TOKEN_COOKIE, '');
        $request->cookies->set(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE, 'partial-access-token-value');

        self::assertSame('partial-access-token-value', $this->extractor->extract($request));
    }

    public function testExtractReturnsFalseWhenTokenCookiesAreEmpty(): void
    {
        $request = Request::create('/api/me');
        $request->cookies->set(CookieFactory::ACCESS_TOKEN_COOKIE, '');
        $request->cookies->set(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE, '');

        self::assertFalse($this->extractor->extract($request));
    }
}
