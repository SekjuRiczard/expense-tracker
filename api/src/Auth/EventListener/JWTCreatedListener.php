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

namespace App\Auth\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class JWTCreatedListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['lexik_jwt_authentication.on_jwt_created' => 'onJWTCreated'];
    }
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();
        /** @var array<string, mixed> $payload */
        $payload = $event->getData();
        $payload['has_pin'] = $user->getPin() !== null;
        $event->setData($payload);
    }
}