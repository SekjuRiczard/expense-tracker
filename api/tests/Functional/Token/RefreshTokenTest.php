<?php

declare(strict_types=1);

namespace App\Tests\Functional\Token;

use App\Tests\Support\ApiTestCase;
use App\Tests\Support\AuthWorkflow;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

#[Medium]
final class RefreshTokenTest extends ApiTestCase
{
    use AuthWorkflow;

    #[Test]
    public function refresh_token_returns_new_access_and_refresh_tokens(): void
    {
        $user = $this->createAuthenticatedUser('refresh_token');

        $responseData = $this->postJson('/api/token/refresh', [
            'refreshToken' => $user['refreshToken'],
        ]);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('authenticated', $responseData['status'] ?? null);
        self::assertSame('Token refreshed successfully.', $responseData['message'] ?? null);
        $this->assertTokenPayload($responseData, refreshTokenExpected: true);
        $this->assertUserPayload($responseData, hasPin: true);
        self::assertNotSame($user['token'], $responseData['token']);
        self::assertNotSame($user['refreshToken'], $responseData['refreshToken']);
    }

    #[Test]
    public function old_refresh_token_is_invalid_after_rotation(): void
    {
        $user = $this->createAuthenticatedUser('refresh_rotation');
        $oldRefreshToken = $user['refreshToken'];

        $this->postJson('/api/token/refresh', [
            'refreshToken' => $oldRefreshToken,
        ]);
        $this->assertHttpStatus(Response::HTTP_OK);

        $responseData = $this->postJson('/api/token/refresh', [
            'refreshToken' => $oldRefreshToken,
        ]);

        $this->assertErrorResponse(
            responseData: $responseData,
            statusCode: Response::HTTP_UNAUTHORIZED,
            status: 'error',
            message: 'Invalid or expired refresh token.',
        );
    }

    #[Test]
    public function invalid_refresh_token_returns_unauthorized(): void
    {
        $responseData = $this->postJson('/api/token/refresh', [
            'refreshToken' => 'invalid-refresh-token',
        ]);

        $this->assertErrorResponse(
            responseData: $responseData,
            statusCode: Response::HTTP_UNAUTHORIZED,
            status: 'error',
            message: 'Invalid or expired refresh token.',
        );
    }
}
