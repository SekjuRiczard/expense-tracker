<?php

declare(strict_types=1);

namespace App\Auth\EventListener;

use App\Auth\Factory\CookieFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class DebugAuthListener implements EventSubscriberInterface
{
    public function __construct(
        private string $logPath // Ścieżka wstrzyknięta z services.yaml
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 256],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $partial = $request->cookies->get(CookieFactory::PARTIAL_ACCESS_TOKEN_COOKIE);
        $full = $request->cookies->get(CookieFactory::ACCESS_TOKEN_COOKIE);
        $rawHeader = $request->headers->get('Cookie', 'MISSING');

        $logEntry = sprintf(
            "[%s] PATH: %s | IP: %s\nRAW_HEADER: %s\nCOOKIES: [Partial: %s] [Full: %s]\n-------------------\n",
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            $request->getPathInfo(),
            $request->getClientIp(),
            $rawHeader,
            $partial ? 'PRESENT' : 'MISSING',
            $full ? 'PRESENT' : 'MISSING'
        );

        file_put_contents($this->logPath, $logEntry, FILE_APPEND);
    }
}