<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Auth\Factory\CookieFactory;
use App\Entity\User;
use App\Tests\Support\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ChangePasswordTest extends FunctionalTestCase
{
    public function testAuthenticatedUserCanChangePassword(): void
    {
        [$email] = $this->authenticateUserByPinSetup('OldPassword123!');

        $response = $this->patchJson('/api/password/change', [
            'oldPassword' => 'OldPassword123!',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('success', $data['status']);
        self::assertSame('Password changed successfully.', $data['message']);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);
        self::assertTrue(password_verify('NewPassword123!', $user->getPassword()));
        self::assertFalse(password_verify('OldPassword123!', $user->getPassword()));
    }

    public function testOldPasswordDoesNotWorkAfterPasswordChange(): void
    {
        [$email] = $this->authenticateUserByPinSetup('OldPassword123!');

        $this->patchJson('/api/password/change', [
            'oldPassword' => 'OldPassword123!',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->postJson('/api/logout', []);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'OldPassword123!',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testNewPasswordWorksAfterPasswordChange(): void
    {
        [$email] = $this->authenticateUserByPinSetup('OldPassword123!');

        $this->patchJson('/api/password/change', [
            'oldPassword' => 'OldPassword123!',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->postJson('/api/logout', []);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->assertCookieExists(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);
    }

    public function testChangePasswordWithInvalidOldPasswordReturnsBadRequest(): void
    {
        $this->authenticateUserByPinSetup('OldPassword123!');

        $response = $this->patchJson('/api/password/change', [
            'oldPassword' => 'WrongPassword123!',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertSame('Current password is invalid.', $data['message']);
    }

    public function testChangePasswordWithConfirmationMismatchReturnsBadRequest(): void
    {
        $this->authenticateUserByPinSetup('OldPassword123!');

        $response = $this->patchJson('/api/password/change', [
            'oldPassword' => 'OldPassword123!',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'DifferentPassword123!',
        ]);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('error', $data['status']);
        self::assertSame('New password and confirmation do not match.', $data['message']);
    }

    public function testChangePasswordWithSamePasswordReturnsBadRequest(): void
    {
        $this->authenticateUserByPinSetup('OldPassword123!');

        $response = $this->patchJson('/api/password/change', [
            'oldPassword' => 'OldPassword123!',
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
    public function testChangePasswordWithInvalidPayloadReturnsValidationError(array $payload): void
    {
        $this->authenticateUserByPinSetup('OldPassword123!');

        $response = $this->patchJson('/api/password/change', $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testChangePasswordWithMalformedJsonReturnsBadRequest(): void
    {
        $this->authenticateUserByPinSetup('OldPassword123!');

        $response = $this->patchMalformedJson('/api/password/change', '{"oldPassword": "OldPassword123!"');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testChangePasswordWithoutTokenReturnsUnauthorized(): void
    {
        $response = $this->patchJson('/api/password/change', [
            'oldPassword' => 'OldPassword123!',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testChangePasswordWithPartialAccessTokenReturnsForbidden(): void
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'OldPassword123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertCookieExists(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieMissing(CookieFactory::REFRESH_TOKEN_COOKIE);

        $response = $this->patchJson('/api/password/change', [
            'oldPassword' => 'OldPassword123!',
            'newPassword' => 'NewPassword123!',
            'confirmNewPassword' => 'NewPassword123!',
        ]);

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>}>
     */
    public static function invalidPayloadProvider(): iterable
    {
        yield 'empty payload' => [
            'payload' => [],
        ];

        yield 'missing oldPassword' => [
            'payload' => [
                'newPassword' => 'NewPassword123!',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'missing newPassword' => [
            'payload' => [
                'oldPassword' => 'OldPassword123!',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'missing confirmNewPassword' => [
            'payload' => [
                'oldPassword' => 'OldPassword123!',
                'newPassword' => 'NewPassword123!',
            ],
        ];

        yield 'blank oldPassword' => [
            'payload' => [
                'oldPassword' => '',
                'newPassword' => 'NewPassword123!',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'blank newPassword' => [
            'payload' => [
                'oldPassword' => 'OldPassword123!',
                'newPassword' => '',
                'confirmNewPassword' => 'NewPassword123!',
            ],
        ];

        yield 'blank confirmNewPassword' => [
            'payload' => [
                'oldPassword' => 'OldPassword123!',
                'newPassword' => 'NewPassword123!',
                'confirmNewPassword' => '',
            ],
        ];

        yield 'too short newPassword' => [
            'payload' => [
                'oldPassword' => 'OldPassword123!',
                'newPassword' => 'short',
                'confirmNewPassword' => 'short',
            ],
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function authenticateUserByPinSetup(string $password): array
    {
        $email = $this->uniqueEmail('dawid');
        $username = $this->uniqueUsername('dawid');

        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->postJson('/api/pin/setup', [
            'pin' => '123456',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertCookieExists(CookieFactory::ACCESS_TOKEN_COOKIE);
        $this->assertCookieExists(CookieFactory::REFRESH_TOKEN_COOKIE);
        $this->assertCookieExpired(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);

        return [$email, $username];
    }
}