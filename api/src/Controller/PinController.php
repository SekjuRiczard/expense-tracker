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

namespace App\Controller;

use App\Dto\ChangePinRequest;
use App\Dto\SetupPinRequest;
use App\Entity\User;
use App\Service\PinService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/pin', name: 'api_pin_')]
class PinController extends AbstractController
{
    public function __construct(
        private readonly PinService $pinService
    ) {}

    #[Route('/setup', name: 'setup', methods: ['POST'])]
    public function setupPin(
        #[MapRequestPayload] SetupPinRequest $dto,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->pinService->setupPin($user, $dto->pin);

        return $this->json(['message' => 'PIN successfully set up.']);
    }

    #[Route('/verify', name: 'verify', methods: ['POST'])]
    public function verifyPin(
        #[MapRequestPayload] SetupPinRequest $dto,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        $isValid = $this->pinService->verifyPin($user, $dto->pin);
        if (!$isValid) {
            return $this->json(['error' => 'Invalid PIN.'], 403);
        }

        return $this->json(['message' => 'PIN verified successfully.']);
    }

    #[Route('/change', name: 'change', methods: ['PUT'])]
    public function changePin(
        #[MapRequestPayload] ChangePinRequest $dto,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        $this->pinService->changePin($user, $dto->oldPin, $dto->newPin);

        return $this->json(['message' => 'PIN successfully changed.']);
    }
}