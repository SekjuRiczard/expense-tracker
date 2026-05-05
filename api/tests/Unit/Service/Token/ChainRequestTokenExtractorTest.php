<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token;

use App\Auth\Service\Token\Extractor\BearerRequestTokenExtractor;
use App\Auth\Service\Token\Extractor\ChainRequestTokenExtractor;
use App\Auth\Service\Token\Extractor\CookieAccessTokenExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class ChainRequestTokenExtractorTest extends TestCase
{
    #[Test]
    public function it_uses_first_matching_extractor(): void
    {
        $request = new Request(cookies: ['access_token' => 'cookie-token']);
        $request->headers->set('Authorization', 'Bearer bearer-token');

        $extractor = new ChainRequestTokenExtractor([
            new BearerRequestTokenExtractor(),
            new CookieAccessTokenExtractor(),
        ]);

        self::assertSame('bearer-token', $extractor->extract($request));
    }

    #[Test]
    public function it_falls_back_to_next_extractor(): void
    {
        $request = new Request(cookies: ['access_token' => 'cookie-token']);

        $extractor = new ChainRequestTokenExtractor([
            new BearerRequestTokenExtractor(),
            new CookieAccessTokenExtractor(),
        ]);

        self::assertSame('cookie-token', $extractor->extract($request));
    }

    #[Test]
    public function it_throws_last_unauthorized_exception_when_no_extractor_matches(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Missing "access_token" cookie.');

        $extractor = new ChainRequestTokenExtractor([
            new BearerRequestTokenExtractor(),
            new CookieAccessTokenExtractor(),
        ]);

        $extractor->extract(new Request());
    }
}
