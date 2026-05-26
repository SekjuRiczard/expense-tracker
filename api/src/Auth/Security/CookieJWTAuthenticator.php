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

namespace App\Auth\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;

final class CookieJWTAuthenticator extends JWTAuthenticator
{
    protected function getTokenExtractor(): TokenExtractorInterface
    {
        return new CookieTokenExtractor();
    }
}
