<?php

declare(strict_types=1);

namespace App\Service\Token\Extractor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class ChainRequestTokenExtractor implements RequestTokenExtractorInterface
{
    /**
     * @param iterable<RequestTokenExtractorInterface> $extractors
     */
    public function __construct(
        private iterable $extractors,
    ) {
    }

    public function extract(Request $request): string
    {
        $lastException = null;

        foreach ($this->extractors as $extractor) {
            try {
                return $extractor->extract($request);
            } catch (UnauthorizedHttpException $exception) {
                $lastException = $exception;
            }
        }

        if ($lastException instanceof UnauthorizedHttpException) {
            throw $lastException;
        }

        throw new UnauthorizedHttpException('Bearer', 'Missing authentication token.');
    }
}
