<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Entity\PasswordResetCode;
use App\Entity\User;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ResetPasswordTest extends FunctionalTestCase
{
    public function testResetPasswordWithValidCodeChangesPasswordAndMarksCodeAsUsed(): void
    {
        $email = $this->uniqueEmail('dawid');

        $user = $this->createUser(
            email: $email,
            username: $this->uniqueUsername('dawid'),
            plainPassword: 'OldPassword123!',
        );

        $resetCode = $this->createPasswordResetCode($user, '123456');

        $response = $this->postJson('/api/password/reset', [
            'email' => $email,
            'code' => '123456',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('success', $data['status']);
        self::assertSame('Password has been reset successfully.', $data['message']);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);
        self::assertTrue(password_verify('NewPassword123!', $user->getPassword()));
        self::assertFalse(password_verify('OldPassword123!', $user->getPassword()));

        $this->entityManager->refresh($resetCode);

        self::assertNotNull($resetCode->getUsedAt());
    }

    public function testOldPasswordDoesNotWorkAfterPasswordReset(): void
    {
        $email = $this->uniqueEmail('dawid');

        $this->createUser(
            email: $email,
            username: $this->uniqueUsername('dawid'),
            plainPassword: 'OldPassword123!',
        );

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $this->createPasswordResetCode($user, '123456');

        $this->postJson('/api/password/reset', [
            'email' => $email,
            'code' => '123456',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'OldPassword123!',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testNewPasswordWorksAfterPasswordReset(): void
    {
        $email = $this->uniqueEmail('dawid');

        $this->createUser(
            email: $email,
            username: $this->uniqueUsername('dawid'),
            plainPassword: 'OldPassword123!',
        );

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        $this->createPasswordResetCode($user, '123456');

        $this->postJson('/api/password/reset', [
            'email' => $email,
            'code' => '123456',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testResetPasswordWithInvalidCodeReturnsBadRequest(): void
    {
        $email = $this->uniqueEmail('dawid');

        $user = $this->createUser(
            email: $email,
            username: $this->uniqueUsername('dawid'),
            plainPassword: 'OldPassword123!',
        );

        $this->createPasswordResetCode($user, '123456');

        $response = $this->postJson('/api/password/reset', [
            'email' => $email,
            'code' => '654321',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertSame('Invalid or expired password reset code.', $data['message']);
    }

    public function testResetPasswordWithExpiredCodeReturnsBadRequest(): void
    {
        $email = $this->uniqueEmail('dawid');

        $user = $this->createUser(
            email: $email,
            username: $this->uniqueUsername('dawid'),
            plainPassword: 'OldPassword123!',
        );

        $this->createPasswordResetCode(
            user: $user,
            code: '123456',
            expiresAt: (new \DateTimeImmutable())->modify('-1 minute'),
        );

        $response = $this->postJson('/api/password/reset', [
            'email' => $email,
            'code' => '123456',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertSame('Invalid or expired password reset code.', $data['message']);
    }

    public function testResetPasswordWithUsedCodeReturnsBadRequest(): void
    {
        $email = $this->uniqueEmail('dawid');

        $user = $this->createUser(
            email: $email,
            username: $this->uniqueUsername('dawid'),
            plainPassword: 'OldPassword123!',
        );

        $resetCode = $this->createPasswordResetCode($user, '123456');
        $resetCode->markAsUsed();
        $this->entityManager->flush();

        $response = $this->postJson('/api/password/reset', [
            'email' => $email,
            'code' => '123456',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testResetPasswordWithConfirmationMismatchReturnsBadRequest(): void
    {
        $email = $this->uniqueEmail('dawid');

        $user = $this->createUser(
            email: $email,
            username: $this->uniqueUsername('dawid'),
            plainPassword: 'OldPassword123!',
        );

        $this->createPasswordResetCode($user, '123456');

        $response = $this->postJson('/api/password/reset', [
            'email' => $email,
            'code' => '123456',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'DifferentPassword123!',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertSame('New password and confirmation do not match.', $data['message']);
    }

    public function testResetPasswordWithSamePasswordReturnsBadRequest(): void
    {
        $email = $this->uniqueEmail('dawid');

        $user = $this->createUser(
            email: $email,
            username: $this->uniqueUsername('dawid'),
            plainPassword: 'OldPassword123!',
        );

        $this->createPasswordResetCode($user, '123456');

        $response = $this->postJson('/api/password/reset', [
            'email' => $email,
            'code' => '123456',
            'newPassword' => 'OldPassword123!',
            'confirmNewPassword' => 'OldPassword123!',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertSame('New password must be different from current password.', $data['message']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPayloadProvider')]
    public function testResetPasswordWithInvalidPayloadReturnsValidationError(array $payload): void
    {
        $response = $this->postJson('/api/password/reset', $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testResetPasswordWithMalformedJsonReturnsBadRequest(): void
    {
        $response = $this->postMalformedJson('/api/password/reset', '{"email": "broken"');

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
                'code' => '123456',
                'newPassword' => 'NewPassword123!',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'missing code' => [
            'payload' => [
                'email' => 'test@example.com',
                'newPassword' => 'NewPassword123!',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'missing newPassword' => [
            'payload' => [
                'email' => 'test@example.com',
                'code' => '123456',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'missing confirmNewPassword' => [
            'payload' => [
                'email' => 'test@example.com',
                'code' => '123456',
                'newPassword' => 'NewPassword123!',
            ],
        ];

        yield 'invalid email' => [
            'payload' => [
                'email' => 'not-an-email',
                'code' => '123456',
                'newPassword' => 'NewPassword123!',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'invalid code letters' => [
            'payload' => [
                'email' => 'test@example.com',
                'code' => 'abcdef',
                'newPassword' => 'NewPassword123!',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'too short code' => [
            'payload' => [
                'email' => 'test@example.com',
                'code' => '12345',
                'newPassword' => 'NewPassword123!',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'too short newPassword' => [
            'payload' => [
                'email' => 'test@example.com',
                'code' => '123456',
                'newPassword' => 'short',
                'confirmNewPassword' => 'short',
            ],
        ];
    }

    private function createPasswordResetCode(
        User $user,
        string $code,
        ?\DateTimeImmutable $expiresAt = null,
    ): PasswordResetCode {
        $resetCode = new PasswordResetCode(
            user: $user,
            codeHash: hash('sha256', $code),
            expiresAt: $expiresAt ?? (new \DateTimeImmutable())->modify('+15 minutes'),
        );

        $this->entityManager->persist($resetCode);
        $this->entityManager->flush();

        return $resetCode;
    }
}