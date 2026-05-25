<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Entity\PasswordResetCode;
use App\Entity\User;
use App\Tests\Support\FunctionalTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class ForgotPasswordTest extends FunctionalTestCase
{
    public function testForgotPasswordForExistingUserReturnsNeutralSuccessAndCreatesResetCode(): void
    {
        $email = $this->uniqueEmail('dawid');

        $user = $this->createUser(
            email: $email,
            username: $this->uniqueUsername('dawid'),
            plainPassword: 'Password123!',
        );

        $response = $this->postJson('/api/password/forgot', [
            'email' => $email,
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('success', $data['status']);
        self::assertSame('If this email exists, password reset code has been sent.', $data['message']);

        $codes = $this->findPasswordResetCodesForUser($user);

        self::assertCount(1, $codes);
        self::assertSame(64, strlen($codes[0]->getCodeHash()));
        self::assertNull($codes[0]->getUsedAt());
        self::assertGreaterThan(new DateTimeImmutable(), $codes[0]->getExpiresAt());
    }

    public function testForgotPasswordForUnknownUserReturnsSameNeutralSuccessAndDoesNotCreateResetCode(): void
    {
        $response = $this->postJson('/api/password/forgot', [
            'email' => $this->uniqueEmail('missing'),
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('success', $data['status']);
        self::assertSame('If this email exists, password reset code has been sent.', $data['message']);

        $codes = $this->entityManager
            ->getRepository(PasswordResetCode::class)
            ->findAll();

        self::assertCount(0, $codes);
    }

    public function testForgotPasswordForInactiveUserReturnsNeutralSuccessAndDoesNotCreateResetCode(): void
    {
        $email = $this->uniqueEmail('inactive');

        $this->createUser(
            email: $email,
            username: $this->uniqueUsername('inactive'),
            plainPassword: 'Password123!',
            isActive: false,
        );

        $response = $this->postJson('/api/password/forgot', [
            'email' => $email,
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('success', $data['status']);
        self::assertSame('If this email exists, password reset code has been sent.', $data['message']);

        $codes = $this->entityManager
            ->getRepository(PasswordResetCode::class)
            ->findAll();

        self::assertCount(0, $codes);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('invalidPayloadProvider')]
    public function testForgotPasswordWithInvalidPayloadReturnsValidationError(array $payload): void
    {
        $response = $this->postJson('/api/password/forgot', $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testForgotPasswordWithMalformedJsonReturnsBadRequest(): void
    {
        $response = $this->postMalformedJson('/api/password/forgot', '{"email": "broken"');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>}>
     */
    public static function invalidPayloadProvider(): iterable
    {
        yield 'empty payload' => [
            'payload' => [],
        ];

        yield 'missing email' => [
            'payload' => [
                'other' => 'test@example.com',
            ],
        ];

        yield 'blank email' => [
            'payload' => [
                'email' => '',
            ],
        ];

        yield 'invalid email' => [
            'payload' => [
                'email' => 'not-an-email',
            ],
        ];
    }

    /**
     * @return list<PasswordResetCode>
     */
    private function findPasswordResetCodesForUser(User $user): array
    {
        /** @var list<PasswordResetCode> $codes */
        $codes = $this->entityManager
            ->getRepository(PasswordResetCode::class)
            ->findBy(['user' => $user]);

        return $codes;
    }
}