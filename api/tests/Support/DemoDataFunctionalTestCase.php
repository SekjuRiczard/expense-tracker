<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Budget\Entity\Budget;
use App\Budget\Repository\BudgetRepository;
use App\Entity\User;
use App\Enum\UserRole;
use App\Transaction\Entity\Transaction;
use App\Transaction\Repository\TransactionRepository;
use App\Wallet\Entity\Wallet;
use App\Wallet\Repository\WalletRepository;
use Symfony\Component\HttpFoundation\Response;

abstract class DemoDataFunctionalTestCase extends FunctionalTestCase
{
    protected function authenticateAdmin(
        string $email = 'admin-demo-data@example.com',
        string $username = 'admin-demo-data',
    ): User {
        return $this->authenticateUserWithRole(
            email: $email,
            username: $username,
            role: UserRole::ADMIN,
        );
    }

    protected function authenticateRegularUser(
        string $email = 'user-demo-data@example.com',
        string $username = 'user-demo-data',
    ): User {
        return $this->authenticateUserWithRole(
            email: $email,
            username: $username,
        );
    }

    protected function deleteJson(string $uri): Response
    {
        $this->client->request(
            method: 'DELETE',
            uri: $uri,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $this->clientIp,
            ],
        );

        return $this->client->getResponse();
    }

    /**
     * @return list<Wallet>
     */
    protected function findWalletsForUser(User $user): array
    {
        return static::getContainer()
            ->get(WalletRepository::class)
            ->findBy(['user' => $user]);
    }

    /**
     * @return list<Budget>
     */
    protected function findBudgetsForUser(User $user): array
    {
        return static::getContainer()
            ->get(BudgetRepository::class)
            ->findBy(['user' => $user]);
    }

    /**
     * @return list<Transaction>
     */
    protected function findTransactionsForUser(User $user): array
    {
        return static::getContainer()
            ->get(TransactionRepository::class)
            ->findBy(['user' => $user]);
    }

    private function authenticateUserWithRole(
        string $email,
        string $username,
        ?UserRole $role = null,
    ): User {
        $response = $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        if (null !== $role) {
            $user->setRoles($role);
            $this->entityManager->flush();
        }

        $response = $this->postJson('/api/pin/setup', [
            'pin' => '123456',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        return $user;
    }
}