<?php

declare(strict_types=1);

namespace App\Tests\Functional\Session;

use App\Tests\Support\ApiTestCase;
use App\Tests\Support\AuthWorkflow;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

#[Medium]
final class CurrentSessionTest extends ApiTestCase
{
    use AuthWorkflow;

    #[Test]
    public function me_returns_pin_setup_required_for_partial_setup_session(): void
    {
        $registerResponse = $this->registerUser();

        $responseData = $this->getJson('/api/me', (string) $registerResponse['token']);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('pin_setup_required', $responseData['status'] ?? null);
        $this->assertUserPayload($responseData, hasPin: false);
    }

    #[Test]
    public function me_returns_pin_verification_required_for_login_partial_session(): void
    {
        $user = $this->createUserWaitingForPinVerification('me_pin_verification');

        $responseData = $this->getJson('/api/me', (string) $user['token']);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('pin_verification_required', $responseData['status'] ?? null);
        $this->assertUserPayload($responseData, hasPin: true);
    }

    #[Test]
    public function me_returns_authenticated_for_full_session(): void
    {
        $user = $this->createAuthenticatedUser('me_authenticated');

        $responseData = $this->getJson('/api/me', (string) $user['token']);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('authenticated', $responseData['status'] ?? null);
        $this->assertUserPayload($responseData, hasPin: true);
    }

    #[Test]
    public function me_returns_unauthorized_without_token(): void
    {
        $responseData = $this->getJson('/api/me');

        $this->assertHttpStatus(Response::HTTP_UNAUTHORIZED);
        self::assertArrayHasKey('message', $responseData);
    }
}
