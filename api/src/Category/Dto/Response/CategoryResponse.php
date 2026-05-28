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

namespace App\Category\Dto\Response;

use App\Category\Entity\Category;
use DateTimeImmutable;
use LogicException;

final readonly class CategoryResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public bool $isDefault,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(Category $category): self
    {
        return new self(
            $category->getId() ?? throw new LogicException('Category ID is required.'),
            $category->getName(),
            $category->getType()->value,
            $category->isDefault(),
            $category->getCreatedAt(),
            $category->getUpdatedAt(),
        );
    }
}