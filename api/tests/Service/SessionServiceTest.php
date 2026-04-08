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

namespace App\Tests\Service;

use App\Entity\Session;
use App\Entity\User;
use App\Repository\Session\SessionRepository;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SessionServiceTest extends TestCase
{
    #[Test]
    public function it_creates_session(): void
    {
        /** @var EntityManagerInterface&MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $this->assertSame('hash', (new SessionService($em, $this->createMock(SessionRepository::class)))->createSession($this->createMock(User::class), 'hash', '127.0.0.1', 'agent')->getTokenHash());
    }
    #[Test]
    public function it_removes_entity_when_found(): void
    {
        /** @var Session&MockObject $session */
        $session = $this->createMock(Session::class);
        /** @var EntityManagerInterface&MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('remove')->with($session);
        $em->expects($this->once())->method('flush');
        (new SessionService($em, $this->createConfiguredMock(SessionRepository::class, ['findOneBy' => $session])))->deleteSession('hash');
    }
}