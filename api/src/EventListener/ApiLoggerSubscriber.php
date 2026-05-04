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

namespace App\EventListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
final class ApiLoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(#[Autowire(service: 'monolog.logger.api')] private readonly LoggerInterface $logger) {}
    public static function getSubscribedEvents(): array
    {

        return [KernelEvents::RESPONSE => ['onResponse', -255]];
    }
    public function onResponse(ResponseEvent $event): void
    {
        if (str_contains($event->getRequest()->getPathInfo(), '/api')) {
            $this->logger->info(sprintf("\nCZAS: %s\nURL: %s\nSTATUS: %d\nPAYLOAD:\n%s\nRESPONSE:\n%s", (new \DateTimeImmutable())->format('Y-m-d H:i:s.v'), $event->getRequest()->getUri(), $event->getResponse()->getStatusCode(), json_encode(json_decode($event->getRequest()->getContent() ?: '{}', true) ?? ['raw' => $event->getRequest()->getContent()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), json_encode(json_decode($event->getResponse()->getContent() ?: '{}', true) ?? ['raw' => $event->getResponse()->getContent()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
        }
    }
}