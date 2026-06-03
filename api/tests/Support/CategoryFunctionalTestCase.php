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

namespace App\Tests\Support;

use App\Category\Entity\Category;
use App\Category\Enum\CategoryType;
use App\Category\Repository\CategoryRepository;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

abstract class CategoryFunctionalTestCase extends FunctionalTestCase
{
    protected function authenticateUser(
        string $email = 'category-owner@example.com',
        string $username = 'category-owner',
    ): User {
        $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => 'Password123!',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->postJson('/api/pin/setup', [
            'pin' => '123456',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $user = $this->findUserByEmail($email);

        self::assertInstanceOf(User::class, $user);

        return $user;
    }

    protected function createCategory(
        ?User $user,
        string $name = 'Subskrypcje',
        CategoryType $type = CategoryType::EXPENSE,
        bool $isDefault = false,
    ): Category {
        $category = new Category(
            user: $user,
            name: $name,
            type: $type,
            isDefault: $isDefault,
        );

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    protected function createDefaultCategory(
        string $name = 'Jedzenie',
        CategoryType $type = CategoryType::EXPENSE,
    ): Category {
        return $this->createCategory(
            user: null,
            name: $name,
            type: $type,
            isDefault: true,
        );
    }

    protected function createUserCategory(
        User $user,
        string $name = 'Subskrypcje',
        CategoryType $type = CategoryType::EXPENSE,
    ): Category {
        return $this->createCategory(
            user: $user,
            name: $name,
            type: $type,
            isDefault: false,
        );
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    protected function createCategoryThroughApi(array $payload = []): array
    {
        $response = $this->postJson('/api/categories', $payload + [
            'name' => 'Subskrypcje',
            'type' => 'expense',
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertIsInt($data['id']);

        return $data;
    }

    protected function getJson(string $uri): Response
    {
        $this->client->request(
            method: 'GET',
            uri: $uri,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => $this->clientIp,
            ],
        );

        return $this->client->getResponse();
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

    protected function findCategory(int $id): ?Category
    {
        return static::getContainer()
            ->get(CategoryRepository::class)
            ->find($id);
    }

    protected function findCategoryFresh(int $id): ?Category
    {
        $this->entityManager->clear();

        return $this->findCategory($id);
    }
}
