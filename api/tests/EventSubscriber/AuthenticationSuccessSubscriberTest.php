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

namespace App\Tests\EventSubscriber;

use App\Entity\User;
use App\EventSubscriber\AuthenticationSuccessSubscriber;
use App\Service\SessionManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
final class AuthenticationSuccessSubscriberTest extends TestCase
{
    #[Test]
    public function it_creates_session_on_authentication_success(): void
    {
        /** @var User&MockObject $user */
        $user = $this->createMock(User::class);
        /** @var SessionManagerInterface&MockObject $manager */
        $manager = $this->createMock(SessionManagerInterface::class);
        $manager->expects($this->once())->method('createSession')->with($user, hash('sha256', 'plain-token'), '192.168.1.1', 'test-agent');
        (new AuthenticationSuccessSubscriber($manager, $this->createConfiguredMock(RequestStack::class, ['getCurrentRequest' => new Request(server: ['REMOTE_ADDR' => '192.168.1.1', 'HTTP_USER_AGENT' => 'test-agent'])])))->onAuthenticationSuccess($this->createConfiguredMock(AuthenticationSuccessEvent::class, ['getUser' => $user, 'getData' => ['token' => 'plain-token']]));
    }
}