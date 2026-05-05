<?php

declare(strict_types=1);

namespace App\Auth\Service\Token\Refresh;

use Symfony\Component\HttpFoundation\Request;

final readonly class ChainRefreshTokenResolver implements RefreshTokenResolverInterface
{
    /**
     * @param iterable<RefreshTokenResolverInterface> $resolvers
     */
    public function __construct(
        private iterable $resolvers,
    ) {
    }

    public function resolve(Request $request): ?string
    {
        foreach ($this->resolvers as $resolver) {
            $refreshToken = $resolver->resolve($request);

            if ($refreshToken !== null) {
                return $refreshToken;
            }
        }

        return null;
    }
}
