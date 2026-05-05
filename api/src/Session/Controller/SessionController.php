<?php

/*
 * This file is part of the Expense Tracker.
 *
 * (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Session\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Session\Dto\Response\SessionResponse;
use App\Session\Service\Contract\SessionManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth/sessions')]
class SessionController extends AbstractController
{
    #[Route('', name: 'api_session_list', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json(array_map(fn(Session $s): SessionResponse => SessionResponse::fromEntity($s), $user->getSessions()->toArray()));
    }
    #[Route('/{id}', name: 'api_session_delete', methods: ['DELETE'])]
    public function delete(Session $session, #[CurrentUser] User $user, SessionManagerInterface $sessionManager): JsonResponse
    {
        if ($session->getUser() !== $user) throw $this->createAccessDeniedException('Access denied.');
        $sessionManager->deleteSession($session->getTokenHash());

        return $this->json(null, 204);
    }
}