<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token;

use App\Auth\Service\Token\Refresh\BodyRefreshTokenResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class BodyRefreshTokenResolverTest extends TestCase
{
    #[Test]
    public function it_resolves_refresh_token_from_json_body(): void
    {
        $request = Request::create(
            uri: '/api/token/refresh',
            method: 'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['refreshToken' => 'refresh-token'], JSON_THROW_ON_ERROR),
        );

        self::assertSame('refresh-token', (new BodyRefreshTokenResolver())->resolve($request));
    }

    #[Test]
    public function it_trims_refresh_token_from_json_body(): void
    {
        $request = Request::create(
            uri: '/api/token/refresh',
            method: 'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['refreshToken' => '  refresh-token  '], JSON_THROW_ON_ERROR),
        );

        self::assertSame('refresh-token', (new BodyRefreshTokenResolver())->resolve($request));
    }

    #[Test]
    public function it_returns_null_when_refresh_token_is_missing(): void
    {
        $request = Request::create(
            uri: '/api/token/refresh',
            method: 'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([], JSON_THROW_ON_ERROR),
        );

        self::assertNull((new BodyRefreshTokenResolver())->resolve($request));
    }

    #[Test]
    public function it_returns_null_for_invalid_json_body(): void
    {
        $request = Request::create(
            uri: '/api/token/refresh',
            method: 'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{invalid-json',
        );

        self::assertNull((new BodyRefreshTokenResolver())->resolve($request));
    }
}
