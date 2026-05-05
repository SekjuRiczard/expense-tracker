<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token;

use App\Auth\Service\Token\Extractor\CookieAccessTokenExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class CookieAccessTokenExtractorTest extends TestCase
{
    #[Test]
    public function it_extracts_access_token_from_cookie(): void
    {
        $request = new Request(cookies: ['access_token' => 'cookie-token']);

        self::assertSame('cookie-token', (new CookieAccessTokenExtractor())->extract($request));
    }

    #[Test]
    public function it_supports_custom_cookie_name(): void
    {
        $request = new Request(cookies: ['custom_access_token' => 'cookie-token']);

        self::assertSame('cookie-token', (new CookieAccessTokenExtractor('custom_access_token'))->extract($request));
    }

    #[Test]
    public function it_rejects_missing_cookie(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Missing "access_token" cookie.');

        (new CookieAccessTokenExtractor())->extract(new Request());
    }

    #[Test]
    public function it_rejects_empty_cookie(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Missing "access_token" cookie.');

        $request = new Request(cookies: ['access_token' => '   ']);

        (new CookieAccessTokenExtractor())->extract($request);
    }
}
