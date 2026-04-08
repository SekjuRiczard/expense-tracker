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

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\SessionManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly SessionManagerInterface $sessionManager, private readonly RequestStack $requestStack) {}
    public static function getSubscribedEvents(): array
    {
        return [Events::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess'];
    }
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        /** @var ?User $user */
        $user = $event->getUser();
        /** @var array $data */
        $data = $event->getData();
        if (!$user instanceof User || !isset($data['token'])) return;
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $this->sessionManager->createSession($user, hash('sha256', (string) $data['token']), $request->getClientIp(), $request->headers->get('User-Agent'));
    }
}