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

namespace App\Auth\Controller;

use App\Entity\Session;
use App\Session\Service\SessionManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final readonly class LogoutController
{
    public function __construct(
        private SessionManagerInterface $sessionManager,
    ) {
    }
    #[Route('/api/logout', name: 'auth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        /** @var Session|null $session */
        $session = $request->attributes->get('app_session');
        if ($session instanceof Session) {
            $this->sessionManager->revokeSession($session);
        }
        $request->attributes->set('_logout', true);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
