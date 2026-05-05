<?php

declare(strict_types=1);

namespace App\Tests\Functional\Session;

use App\Tests\Support\ApiTestCase;
use App\Tests\Support\AuthWorkflow;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

#[Medium]
final class LogoutTest extends ApiTestCase
{
    use AuthWorkflow;

    #[Test]
    public function logout_revokes_current_session(): void
    {
        $user = $this->createAuthenticatedUser('logout');
        $fullToken = $user['token'];

        $logoutResponse = $this->postJson('/api/logout', [], $fullToken);

        $this->assertHttpStatus(Response::HTTP_OK);
        self::assertSame('success', $logoutResponse['status'] ?? null);
        self::assertSame('Logged out successfully.', $logoutResponse['message'] ?? null);

        $responseData = $this->putJson('/api/pin/change', [
            'oldPin' => self::DEFAULT_PIN,
            'newPin' => '654321',
        ], $fullToken);

        $this->assertUnauthorizedInvalidSession($responseData);
    }
}
