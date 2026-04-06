<?php

/*
 * This file is part of the Expense Tracker.
 *
 * (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
final class AuthenticationTest extends WebTestCase
{
    public function test_it_returns_jwt_token_on_successful_login(): void
    {
        $client = static::createClient();
        $password = 'SuperSecret123!';
        $email = 'jwt_test@example.com';
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => 'jwt_tester',
            'email' => $email,
            'password' => $password
        ]));
        $this->assertResponseStatusCodeSame(201);
        $loginPayload = [
            'email' => $email,
            'password' => $password
        ];
        $client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($loginPayload));
        $this->assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);
        $this->assertArrayHasKey('token', $responseData, 'Response should contain a JWT token.');
    }

    public function test_it_fails_on_invalid_credentials(): void
    {
        $client = static::createClient();
        $loginPayload = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ];
        $client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($loginPayload));
        $this->assertResponseStatusCodeSame(401);
        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);
        $this->assertArrayHasKey('code', $responseData);
        $this->assertEquals(401, $responseData['code']);
        $this->assertEquals('Invalid credentials.', $responseData['message']);
    }
}