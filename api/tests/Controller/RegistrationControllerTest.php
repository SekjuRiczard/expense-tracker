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

use App\Controller\AuthController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;

#[CoversClass(AuthController::class)]
#[Medium]
final class RegistrationControllerTest extends WebTestCase
{
    public function test_it_registers_a_new_user_successfully(): void
    {
        $client = static::createClient();
        $payload = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'SuperSecret123!'
        ];
        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );
        $this->assertResponseStatusCodeSame(201);
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        $responseData = json_decode($responseContent, true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('User created', $responseData['message']);
    }

    public function test_it_fails_when_validation_rules_are_not_met(): void
    {
        $client = static::createClient();
        $payload = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'short'
        ];
        $client->request('POST', '/api/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(422);
    }
}