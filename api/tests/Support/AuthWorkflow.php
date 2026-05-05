<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * Shared auth workflow helpers for functional API tests.
 *
 * Keep endpoint contracts in one place so future HttpOnly migration changes test transport
 * in one helper instead of every test case.
 */
trait AuthWorkflow
{
    /**
     * @return array<string, mixed>
     */
    protected function registerUser(?string $email = null, string $password = self::DEFAULT_PASSWORD): array
    {
        return $this->postJson('/api/register', [
            'username' => 'john',
            'email' => $email ?? $this->uniqueEmail('register'),
            'password' => $password,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function loginUser(string $email, string $password = self::DEFAULT_PASSWORD): array
    {
        return $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function setupPin(string $partialToken, string $pin = self::DEFAULT_PIN): array
    {
        return $this->postJson('/api/pin/setup', [
            'pin' => $pin,
        ], $partialToken);
    }

    /**
     * @return array<string, mixed>
     */
    protected function verifyPin(string $partialToken, string $pin = self::DEFAULT_PIN): array
    {
        return $this->postJson('/api/pin/verify', [
            'pin' => $pin,
        ], $partialToken);
    }

    /**
     * @return array{email: string, password: string, token: string, refreshToken: string}
     */
    protected function createAuthenticatedUser(string $emailPrefix = 'authenticated'): array
    {
        $email = $this->uniqueEmail($emailPrefix);
        $password = self::DEFAULT_PASSWORD;

        $registerResponse = $this->registerUser($email, $password);
        $setupResponse = $this->setupPin((string) $registerResponse['token']);

        return [
            'email' => $email,
            'password' => $password,
            'token' => (string) $setupResponse['token'],
            'refreshToken' => (string) $setupResponse['refreshToken'],
        ];
    }

    /**
     * @return array{email: string, password: string, token: string}
     */
    protected function createUserWaitingForPinVerification(string $emailPrefix = 'pin_verification'): array
    {
        $email = $this->uniqueEmail($emailPrefix);
        $password = self::DEFAULT_PASSWORD;

        $registerResponse = $this->registerUser($email, $password);
        $this->setupPin((string) $registerResponse['token']);
        $loginResponse = $this->loginUser($email, $password);

        return [
            'email' => $email,
            'password' => $password,
            'token' => (string) $loginResponse['token'],
        ];
    }
}
