<?php

/*
 * This file is part of the Expense Tracker.
 *
 *  (c) SekjuRiczard <dawidosak32@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\DemoData\Controller;

use App\DemoData\Service\DemoDataCleaner;
use App\DemoData\Service\DemoDataGenerator;
use App\DemoData\Service\DemoDataStatusProvider;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/api/admin/demo-data', name: 'api_admin_demo_data_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminDemoDataController extends AbstractController
{
    public function __construct(
        private readonly DemoDataGenerator $demoDataGenerator,
        private readonly DemoDataCleaner $demoDataCleaner,
        private readonly DemoDataStatusProvider $demoDataStatusProvider,
    ) {
    }

    #[Route(path: '/status', name: 'status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        return $this->json(
            data: $this->demoDataStatusProvider->getStatus(
                user: $this->getAuthenticatedUser(),
            ),
        );
    }

    #[Route(path: '', name: 'generate', methods: ['POST'])]
    public function generate(): JsonResponse
    {
        return $this->json(
            data: $this->demoDataGenerator->generate(
                user: $this->getAuthenticatedUser(),
            ),
            status: Response::HTTP_CREATED,
        );
    }

    #[Route(path: '', name: 'clear', methods: ['DELETE'])]
    public function clear(): JsonResponse
    {
        return $this->json(
            data: $this->demoDataCleaner->clear(
                user: $this->getAuthenticatedUser(),
            ),
        );
    }

    private function getAuthenticatedUser(): User
    {
        /** @var User|null $user */
        $user = $this->getUser();

        return $user ?? throw new AccessDeniedException();
    }
}
