<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, AuthService $authService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email'], $data['password'], $data['firstName'], $data['lastName'])) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }
        try {
            $user = $authService->register(
                $data['email'],
                $data['password'],
                $data['firstName'],
                $data['lastName']
            );

            return $this->json([
                'message' => 'User registered successfully',
                'user' => $user->getEmail(),
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->json(['error' => 'Registration failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
