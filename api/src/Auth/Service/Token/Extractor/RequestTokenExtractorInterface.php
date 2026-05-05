<?php

declare(strict_types=1);

namespace App\Service\Token\Extractor;

use Symfony\Component\HttpFoundation\Request;

interface RequestTokenExtractorInterface
{
    public function extract(Request $request): string;
}
