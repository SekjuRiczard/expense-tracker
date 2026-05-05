<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Token;

use App\Service\Token\Extractor\BearerRequestTokenExtractor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class BearerRequestTokenExtractorTest extends TestCase
{
    #[Test]
    public function it_extracts_bearer_token(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer access-token');

        self::assertSame('access-token', (new BearerRequestTokenExtractor())->extract($request));
    }

    #[Test]
    public function it_trims_bearer_token(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer  access-token  ');

        self::assertSame('access-token', (new BearerRequestTokenExtractor())->extract($request));
    }

    #[Test]
    public function it_rejects_missing_authorization_header(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Missing Authorization header.');

        (new BearerRequestTokenExtractor())->extract(new Request());
    }

    #[Test]
    public function it_rejects_invalid_authorization_header(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Invalid Authorization header.');

        $request = new Request();
        $request->headers->set('Authorization', 'Token access-token');

        (new BearerRequestTokenExtractor())->extract($request);
    }

    #[Test]
    public function it_rejects_empty_bearer_token(): void
    {
        $this->expectException(UnauthorizedHttpException::class);
        $this->expectExceptionMessage('Missing bearer token.');

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer   ');

        (new BearerRequestTokenExtractor())->extract($request);
    }
}
