<?php

declare(strict_types=1);

namespace App\Tests\Functional\Pin;

use App\Entity\User;
use App\Tests\Support\ApiTestCase;
use App\Tests\Support\AuthWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

#[Medium]
final class PinWorkflowTest extends ApiTestCase
{
    use AuthWorkflow;

    #[Test]
    public function setup_pin_returns_authenticated_session_with_refresh_token(): void
    {
        $registerResponse = $this->registerUser();
        $responseData = $this->setupPin((string) $registerResponse['token']);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('authenticated', $responseData['status'] ?? null);
        self::assertSame('PIN successfully set up.', $responseData['message'] ?? null);
        $this->assertTokenPayload($responseData, refreshTokenExpected: true);
        $this->assertUserPayload($responseData, hasPin: true);
    }

    #[Test]
    public function verify_pin_returns_authenticated_session_with_refresh_token(): void
    {
        $user = $this->createUserWaitingForPinVerification('verify_pin');
        $responseData = $this->verifyPin((string) $user['token']);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('authenticated', $responseData['status'] ?? null);
        self::assertSame('PIN verified successfully.', $responseData['message'] ?? null);
        $this->assertTokenPayload($responseData, refreshTokenExpected: true);
        $this->assertUserPayload($responseData, hasPin: true);
    }

    #[Test]
    public function wrong_pin_returns_forbidden(): void
    {
        $user = $this->createUserWaitingForPinVerification('wrong_pin');
        $responseData = $this->verifyPin((string) $user['token'], '000000');

        $this->assertErrorResponse(
            responseData: $responseData,
            statusCode: Response::HTTP_FORBIDDEN,
            status: 'error',
            message: 'Invalid PIN.',
        );
    }

    #[Test]
    public function three_wrong_pins_lock_user(): void
    {
        $userData = $this->createUserWaitingForPinVerification('pin_lock');
        $partialToken = (string) $userData['token'];

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->verifyPin($partialToken, '000000');
            $this->assertHttpStatus(Response::HTTP_FORBIDDEN);
        }

        $this->verifyPin($partialToken);
        $this->assertHttpStatus(Response::HTTP_FORBIDDEN);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        /** @var User|null $user */
        $user = $entityManager->getRepository(User::class)->findOneBy([
            'email' => $userData['email'],
        ]);

        self::assertNotNull($user);
        self::assertNotNull($user->getPinLockedUntil());
    }

    #[Test]
    public function full_token_can_change_pin(): void
    {
        $user = $this->createAuthenticatedUser('change_pin');

        $responseData = $this->putJson('/api/pin/change', [
            'oldPin' => self::DEFAULT_PIN,
            'newPin' => '654321',
        ], $user['token']);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('success', $responseData['status'] ?? null);
        self::assertSame('PIN successfully changed.', $responseData['message'] ?? null);
    }

    #[Test]
    public function partial_token_cannot_access_pin_change(): void
    {
        $user = $this->createUserWaitingForPinVerification('partial_change_block');

        $responseData = $this->putJson('/api/pin/change', [
            'oldPin' => self::DEFAULT_PIN,
            'newPin' => '654321',
        ], $user['token']);

        $this->assertErrorResponse(
            responseData: $responseData,
            statusCode: Response::HTTP_FORBIDDEN,
            status: 'pin_verification_required',
            message: 'PIN authorization is required to access this resource.',
        );
    }

    #[Test]
    public function old_partial_token_does_not_work_after_setup_pin(): void
    {
        $registerResponse = $this->registerUser();
        $partialToken = (string) $registerResponse['token'];

        $this->setupPin($partialToken);
        $responseData = $this->setupPin($partialToken);

        $this->assertUnauthorizedInvalidSession($responseData);
    }

    #[Test]
    public function old_partial_token_does_not_work_after_verify_pin(): void
    {
        $user = $this->createUserWaitingForPinVerification('old_partial_after_verify');
        $partialToken = (string) $user['token'];

        $this->verifyPin($partialToken);
        $responseData = $this->verifyPin($partialToken);

        $this->assertUnauthorizedInvalidSession($responseData);
    }
}
