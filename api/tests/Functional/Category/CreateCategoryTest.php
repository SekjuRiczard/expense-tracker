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

namespace App\Tests\Functional\Category;

use App\Category\Entity\Category;
use App\Category\Enum\CategoryType;
use App\Tests\Support\CategoryFunctionalTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class CreateCategoryTest extends CategoryFunctionalTestCase
{
    public function testAuthenticatedUserCanCreateCategory(): void
    {
        $user = $this->authenticateUser();

        $response = $this->postJson('/api/categories', [
            'name' => 'Subskrypcje',
            'type' => 'expense',
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertIsInt($data['id']);
        self::assertSame('Subskrypcje', $data['name']);
        self::assertSame('expense', $data['type']);
        self::assertFalse($data['isDefault']);
        self::assertArrayHasKey('createdAt', $data);
        self::assertArrayHasKey('updatedAt', $data);

        $category = $this->findCategoryFresh($data['id']);

        self::assertInstanceOf(Category::class, $category);
        self::assertSame((string) $user->getId(), (string) $category->getUser()?->getId());
        self::assertSame('Subskrypcje', $category->getName());
        self::assertSame(CategoryType::EXPENSE->value, $category->getType()->value);
        self::assertFalse($category->isDefault());
    }

    public function testAuthenticatedUserCanCreateIncomeCategory(): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/categories', [
            'name' => 'Pensja',
            'type' => 'income',
        ]);

        self::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('Pensja', $data['name']);
        self::assertSame('income', $data['type']);
        self::assertFalse($data['isDefault']);

        $category = $this->findCategoryFresh($data['id']);

        self::assertInstanceOf(Category::class, $category);
        self::assertSame(CategoryType::INCOME->value, $category->getType()->value);
    }

    public function testGuestCannotCreateCategory(): void
    {
        $response = $this->postJson('/api/categories', [
            'name' => 'Subskrypcje',
            'type' => 'expense',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $categories = $this->entityManager
            ->getRepository(Category::class)
            ->findAll();

        self::assertCount(0, $categories);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('invalidPayloadProvider')]
    public function testCreateCategoryWithInvalidPayloadReturnsValidationError(array $payload): void
    {
        $this->authenticateUser();

        $response = $this->postJson('/api/categories', $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $categories = $this->entityManager
            ->getRepository(Category::class)
            ->findAll();

        self::assertCount(0, $categories);
    }

    public function testCreateCategoryWithMalformedJsonReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $response = $this->postMalformedJson('/api/categories', '{"name": "Subskrypcje"');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $categories = $this->entityManager
            ->getRepository(Category::class)
            ->findAll();

        self::assertCount(0, $categories);
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>}>
     */
    public static function invalidPayloadProvider(): iterable
    {
        yield 'blank name' => [
            'payload' => [
                'name' => '',
                'type' => 'expense',
            ],
        ];

        yield 'too long name' => [
            'payload' => [
                'name' => str_repeat('a', 51),
                'type' => 'expense',
            ],
        ];

        yield 'missing name' => [
            'payload' => [
                'type' => 'expense',
            ],
        ];

        yield 'missing type' => [
            'payload' => [
                'name' => 'Subskrypcje',
            ],
        ];

        yield 'invalid type' => [
            'payload' => [
                'name' => 'Subskrypcje',
                'type' => 'invalid_type',
            ],
        ];
    }
}
