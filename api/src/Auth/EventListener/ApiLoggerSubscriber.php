<?php

/**
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Auth\EventListener;

use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiLoggerSubscriber implements EventSubscriberInterface
{
    private const REDACTED_PLACEHOLDER = '***';

    /**
     * @var list<string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'oldpassword',
        'newpassword',
        'confirmnewpassword',
        'pin',
        'oldpin',
        'newpin',
        'code',
        'token',
        'access_token',
        'partial_access_token',
        'refresh_token',
    ];

    public function __construct(
        #[Autowire(service: 'monolog.logger.api')]
        private readonly LoggerInterface $apiLogger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['onResponse', -255]];
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $response = $event->getResponse();

        $this->apiLogger->info('API response', [
            'method' => $request->getMethod(),
            'endpoint' => $request->getRequestUri(),
            'statusCode' => $response->getStatusCode(),
            'payload' => $this->redact($this->decodeContent($request->getContent())),
            'response' => $this->redact($this->decodeContent($response->getContent())),
        ]);
    }

    private function decodeContent(string|false $content): mixed
    {
        if (false === $content || '' === trim($content)) {
            return null;
        }

        try {
            return json_decode(
                $content,
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException) {
            return ['raw' => self::REDACTED_PLACEHOLDER];
        }
    }

    private function redact(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $redacted = [];
        foreach ($data as $key => $value) {
            $redacted[$key] = $this->isSensitiveKey($key)
                ? self::REDACTED_PLACEHOLDER
                : $this->redact($value);
        }

        return $redacted;
    }

    private function isSensitiveKey(int|string $key): bool
    {
        return is_string($key)
            && in_array(strtolower($key), self::SENSITIVE_KEYS, true);
    }
}
