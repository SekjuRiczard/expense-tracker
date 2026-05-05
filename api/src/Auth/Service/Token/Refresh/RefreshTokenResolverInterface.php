<?php

declare(strict_types=1);

namespace App\Service\Token\Refresh;

use Symfony\Component\HttpFoundation\Request;

interface RefreshTokenResolverInterface
{
    public function resolve(Request $request): ?string;
}
