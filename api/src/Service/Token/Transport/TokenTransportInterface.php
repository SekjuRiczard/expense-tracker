<?php

declare(strict_types=1);

namespace App\Service\Token\Transport;

use App\Dto\AuthTokenResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

interface TokenTransportInterface
{
    public function apply(JsonResponse $response, AuthTokenResponse $tokenResponse): JsonResponse;
}
