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

namespace App\Tests\EventListener;

use App\Entity\User;
use App\EventListener\JWTCreatedListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class JWTCreatedListenerTest extends TestCase
{
    public function testOnJWTCreatedAddsHasPinClaimWhenUserHasPin(): void
    {
        /** @var User&MockObject $userMock */
        $userMock = $this->createMock(User::class);
        $userMock->method('getPin')->willReturn('zhashowany_pin_1234');
        /** @var JWTCreatedEvent&MockObject $eventMock */
        $eventMock = $this->createMock(JWTCreatedEvent::class);
        $eventMock->method('getUser')->willReturn($userMock);
        $eventMock->method('getData')->willReturn(['username' => 'test@example.com']);
        $eventMock->expects($this->once())
            ->method('setData')
            ->with([
                'username' => 'test@example.com',
                'has_pin' => true
            ]);
        /** @var JWTCreatedListener $listener */
        $listener = new JWTCreatedListener();
        $listener->onJWTCreated($eventMock);
    }

    public function testOnJWTCreatedSetsHasPinToFalseWhenUserHasNoPin(): void
    {
        /** @var User&MockObject $userMock */
        $userMock = $this->createMock(User::class);
        $userMock->method('getPin')->willReturn(null);
        /** @var JWTCreatedEvent&MockObject $eventMock */
        $eventMock = $this->createMock(JWTCreatedEvent::class);
        $eventMock->method('getUser')->willReturn($userMock);
        $eventMock->method('getData')->willReturn(['username' => 'test@example.com']);
        $eventMock->expects($this->once())
            ->method('setData')
            ->with([
                'username' => 'test@example.com',
                'has_pin' => false
            ]);
        /** @var JWTCreatedListener $listener */
        $listener = new JWTCreatedListener();
        $listener->onJWTCreated($eventMock);
    }
}