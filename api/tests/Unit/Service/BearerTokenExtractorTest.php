<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\BearerTokenExtractor;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

#[Small]
final class BearerTokenExtractorTest extends TestCase
{
    #[Test]
    public function it_extracts_bearer_token_from_authorization_header(): void
    {
        $extractor = new BearerTokenExtractor();

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer test-token');

        $token = $extractor->extract($request);

        self::assertSame('test-token', $token);
    }

    #[Test]
    public function it_throws_exception_when_authorization_header_is_missing(): void
    {
        $extractor = new BearerTokenExtractor();

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Missing Authorization header.');

        $extractor->extract(new Request());
    }

    #[Test]
    public function it_throws_exception_when_authorization_header_is_not_bearer(): void
    {
        $extractor = new BearerTokenExtractor();

        $request = new Request();
        $request->headers->set('Authorization', 'Basic test-token');

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Invalid Authorization header.');

        $extractor->extract($request);
    }

    #[Test]
    public function it_throws_exception_when_bearer_token_is_empty(): void
    {
        $extractor = new BearerTokenExtractor();

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ');

        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Missing bearer token.');

        $extractor->extract($request);
    }
}