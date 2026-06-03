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

final class UpdateCategoryTest extends CategoryFunctionalTestCase
{
    public function testAuthenticatedUserCanUpdateCategoryName(): void
    {
        $this->authenticateUser();

        $category = $this->createCategoryThroughApi([
            'name' => 'Stara nazwa',
            'type' => 'expense',
        ]);

        $response = $this->patchJson(sprintf('/api/categories/%d', $category['id']), [
            'name' => 'Nowa nazwa',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('Nowa nazwa', $data['name']);
        self::assertSame('expense', $data['type']);
        self::assertFalse($data['isDefault']);

        $updatedCategory = $this->findCategoryFresh($category['id']);

        self::assertInstanceOf(Category::class, $updatedCategory);
        self::assertSame('Nowa nazwa', $updatedCategory->getName());
        self::assertSame(CategoryType::EXPENSE->value, $updatedCategory->getType()->value);
    }

    public function testAuthenticatedUserCanUpdateCategoryType(): void
    {
        $this->authenticateUser();

        $category = $this->createCategoryThroughApi([
            'name' => 'Subskrypcje',
            'type' => 'expense',
        ]);

        $response = $this->patchJson(sprintf('/api/categories/%d', $category['id']), [
            'type' => 'income',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('Subskrypcje', $data['name']);
        self::assertSame('income', $data['type']);
        self::assertFalse($data['isDefault']);

        $updatedCategory = $this->findCategoryFresh($category['id']);

        self::assertInstanceOf(Category::class, $updatedCategory);
        self::assertSame(CategoryType::INCOME->value, $updatedCategory->getType()->value);
    }

    public function testAuthenticatedUserCanUpdateCategoryNameAndType(): void
    {
        $this->authenticateUser();

        $category = $this->createCategoryThroughApi([
            'name' => 'Subskrypcje',
            'type' => 'expense',
        ]);

        $response = $this->patchJson(sprintf('/api/categories/%d', $category['id']), [
            'name' => 'Pensja',
            'type' => 'income',
        ]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->jsonResponse();

        self::assertSame('Pensja', $data['name']);
        self::assertSame('income', $data['type']);
        self::assertFalse($data['isDefault']);

        $updatedCategory = $this->findCategoryFresh($category['id']);

        self::assertInstanceOf(Category::class, $updatedCategory);
        self::assertSame('Pensja', $updatedCategory->getName());
        self::assertSame(CategoryType::INCOME->value, $updatedCategory->getType()->value);
    }

    public function testUpdateCategoryWithEmptyPayloadReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $category = $this->createCategoryThroughApi([
            'name' => 'Subskrypcje',
            'type' => 'expense',
        ]);

        $response = $this->patchJson(sprintf('/api/categories/%d', $category['id']), []);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $unchangedCategory = $this->findCategoryFresh($category['id']);

        self::assertInstanceOf(Category::class, $unchangedCategory);
        self::assertSame('Subskrypcje', $unchangedCategory->getName());
        self::assertSame(CategoryType::EXPENSE->value, $unchangedCategory->getType()->value);
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('invalidValidationPayloadProvider')]
    public function testUpdateCategoryWithInvalidValidationPayloadReturnsValidationError(array $payload): void
    {
        $this->authenticateUser();

        $category = $this->createCategoryThroughApi([
            'name' => 'Subskrypcje',
            'type' => 'expense',
        ]);

        $response = $this->patchJson(sprintf('/api/categories/%d', $category['id']), $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $unchangedCategory = $this->findCategoryFresh($category['id']);

        self::assertInstanceOf(Category::class, $unchangedCategory);
        self::assertSame('Subskrypcje', $unchangedCategory->getName());
        self::assertSame(CategoryType::EXPENSE->value, $unchangedCategory->getType()->value);
    }

    public function testUpdateCategoryWithInvalidTypeReturnsValidationError(): void
    {
        $this->authenticateUser();

        $category = $this->createCategoryThroughApi();

        $response = $this->patchJson(sprintf('/api/categories/%d', $category['id']), [
            'type' => 'invalid_type',
        ]);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $unchangedCategory = $this->findCategoryFresh($category['id']);

        self::assertInstanceOf(Category::class, $unchangedCategory);
        self::assertSame(CategoryType::EXPENSE->value, $unchangedCategory->getType()->value);
    }

    public function testAuthenticatedUserCannotUpdateDefaultCategory(): void
    {
        $this->authenticateUser();

        $category = $this->createDefaultCategory(
            name: 'Jedzenie',
            type: CategoryType::EXPENSE,
        );

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $response = $this->patchJson(sprintf('/api/categories/%d', $categoryId), [
            'name' => 'Zmieniona systemowa',
        ]);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $unchangedCategory = $this->findCategoryFresh($categoryId);

        self::assertInstanceOf(Category::class, $unchangedCategory);
        self::assertSame('Jedzenie', $unchangedCategory->getName());
    }

    public function testAuthenticatedUserCannotUpdateAnotherUserCategory(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $category = $this->createUserCategory(
            user: $owner,
            name: 'Cudza kategoria',
        );

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $this->authenticateUser(
            email: 'intruder@example.com',
            username: 'intruder',
        );

        $response = $this->patchJson(sprintf('/api/categories/%d', $categoryId), [
            'name' => 'Przejęta kategoria',
        ]);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $unchangedCategory = $this->findCategoryFresh($categoryId);

        self::assertInstanceOf(Category::class, $unchangedCategory);
        self::assertSame('Cudza kategoria', $unchangedCategory->getName());
    }

    public function testUpdateMissingCategoryReturnsNotFound(): void
    {
        $this->authenticateUser();

        $response = $this->patchJson('/api/categories/999999', [
            'name' => 'Nowa nazwa',
        ]);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGuestCannotUpdateCategory(): void
    {
        $owner = $this->createUser(
            email: 'owner@example.com',
            username: 'owner',
        );

        $category = $this->createUserCategory(
            user: $owner,
            name: 'Subskrypcje',
        );

        $categoryId = $category->getId();

        self::assertIsInt($categoryId);

        $response = $this->patchJson(sprintf('/api/categories/%d', $categoryId), [
            'name' => 'Nowa nazwa',
        ]);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $unchangedCategory = $this->findCategoryFresh($categoryId);

        self::assertInstanceOf(Category::class, $unchangedCategory);
        self::assertSame('Subskrypcje', $unchangedCategory->getName());
    }

    public function testUpdateCategoryWithMalformedJsonReturnsBadRequest(): void
    {
        $this->authenticateUser();

        $category = $this->createCategoryThroughApi([
            'name' => 'Subskrypcje',
        ]);

        $response = $this->patchMalformedJson(
            sprintf('/api/categories/%d', $category['id']),
            '{"name": "Nowa nazwa"',
        );

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $unchangedCategory = $this->findCategoryFresh($category['id']);

        self::assertInstanceOf(Category::class, $unchangedCategory);
        self::assertSame('Subskrypcje', $unchangedCategory->getName());
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>}>
     */
    public static function invalidValidationPayloadProvider(): iterable
    {
        yield 'blank name' => [
            'payload' => [
                'name' => '',
            ],
        ];

        yield 'too long name' => [
            'payload' => [
                'name' => str_repeat('a', 51),
            ],
        ];
    }
}
