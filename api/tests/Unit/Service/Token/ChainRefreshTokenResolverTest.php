<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token;

use App\Service\Token\Refresh\ChainRefreshTokenResolver;
use App\Service\Token\Refresh\RefreshTokenResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ChainRefreshTokenResolverTest extends TestCase
{
    #[Test]
    public function it_returns_first_resolved_refresh_token(): void
    {
        $request = Request::create('/api/token/refresh', 'POST');

        $first = $this->createResolver(null);
        $second = $this->createResolver('refresh-token');
        $third = $this->createResolver('other-refresh-token');

        $resolver = new ChainRefreshTokenResolver([$first, $second, $third]);

        self::assertSame('refresh-token', $resolver->resolve($request));
    }

    #[Test]
    public function it_returns_null_when_no_resolver_finds_token(): void
    {
        $request = Request::create('/api/token/refresh', 'POST');

        $resolver = new ChainRefreshTokenResolver([
            $this->createResolver(null),
            $this->createResolver(null),
        ]);

        self::assertNull($resolver->resolve($request));
    }

    private function createResolver(?string $token): RefreshTokenResolverInterface
    {
        return new class($token) implements RefreshTokenResolverInterface {
            public function __construct(
                private readonly ?string $token,
            ) {
            }

            public function resolve(Request $request): ?string
            {
                return $this->token;
            }
        };
    }
}
