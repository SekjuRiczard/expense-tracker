<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Support\ApiTestCase;
use App\Tests\Support\AuthWorkflow;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

#[Medium]
final class LoginTest extends ApiTestCase
{
    use AuthWorkflow;

    #[Test]
    public function login_user_without_pin_returns_pin_setup_required(): void
    {
        $email = $this->uniqueEmail('login_without_pin');
        $password = self::DEFAULT_PASSWORD;

        $this->registerUser($email, $password);
        $responseData = $this->loginUser($email, $password);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('pin_setup_required', $responseData['status'] ?? null);
        self::assertSame('Password verified. PIN setup required.', $responseData['message'] ?? null);
        $this->assertTokenPayload($responseData, refreshTokenExpected: false);
        $this->assertUserPayload($responseData, hasPin: false);
    }

    #[Test]
    public function login_user_with_pin_returns_pin_verification_required(): void
    {
        $email = $this->uniqueEmail('login_with_pin');
        $password = self::DEFAULT_PASSWORD;

        $registerResponse = $this->registerUser($email, $password);
        $this->setupPin((string) $registerResponse['token']);
        $responseData = $this->loginUser($email, $password);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('pin_verification_required', $responseData['status'] ?? null);
        self::assertSame('Password verified. PIN verification required.', $responseData['message'] ?? null);
        $this->assertTokenPayload($responseData, refreshTokenExpected: false);
        $this->assertUserPayload($responseData, hasPin: true);
    }

    #[Test]
    public function login_with_invalid_credentials_returns_unauthorized(): void
    {
        $responseData = $this->loginUser(
            email: $this->uniqueEmail('invalid_login'),
            password: 'wrong-password',
        );

        $this->assertErrorResponse(
            responseData: $responseData,
            statusCode: Response::HTTP_UNAUTHORIZED,
            status: 'error',
            message: 'Invalid email or password.',
        );
    }

    #[Test]
    public function login_is_rate_limited_after_too_many_attempts(): void
    {
        $email = $this->uniqueEmail('rate_limit');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $responseData = $this->loginUser($email, 'wrong-password');

            $this->assertErrorResponse(
                responseData: $responseData,
                statusCode: Response::HTTP_UNAUTHORIZED,
                status: 'error',
                message: 'Invalid email or password.',
            );
        }

        $responseData = $this->loginUser($email, 'wrong-password');

        $this->assertErrorResponse(
            responseData: $responseData,
            statusCode: Response::HTTP_TOO_MANY_REQUESTS,
            status: 'error',
            message: 'Too many login attempts. Try again later.',
        );
    }
}
