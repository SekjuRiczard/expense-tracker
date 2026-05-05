<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Support\ApiTestCase;
use App\Tests\Support\AuthWorkflow;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

#[Medium]
final class RegisterTest extends ApiTestCase
{
    use AuthWorkflow;

    #[Test]
    public function register_returns_pin_setup_required_partial_token(): void
    {
        $responseData = $this->registerUser();

        $this->assertHttpStatus(Response::HTTP_CREATED);
        self::assertSame('pin_setup_required', $responseData['status'] ?? null);
        self::assertSame('User created. PIN setup required.', $responseData['message'] ?? null);
        $this->assertTokenPayload($responseData, refreshTokenExpected: false);
        $this->assertUserPayload($responseData, hasPin: false);
    }

    #[Test]
    public function register_with_existing_email_returns_conflict(): void
    {
        $email = $this->uniqueEmail('existing');

        $this->registerUser($email);
        $responseData = $this->registerUser($email);

        $this->assertErrorResponse(
            responseData: $responseData,
            statusCode: Response::HTTP_CONFLICT,
            status: 'error',
            message: sprintf('User with this email ("%s") already exists.', $email),
        );
    }
}
