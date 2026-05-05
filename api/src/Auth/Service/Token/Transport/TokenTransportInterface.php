<?php

declare(strict_types=1);

namespace App\Auth\Service\Token\Transport;

use App\Auth\Dto\Response\AuthTokenResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

interface TokenTransportInterface
{
    public function apply(JsonResponse $response, AuthTokenResponse $tokenResponse): JsonResponse;
}
