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

namespace App\Category\Dto\Request;

use App\Category\Enum\CategoryType;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCategoryRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Category name cannot be blank.')]
        #[Assert\Length(
            max: 50,
            maxMessage: 'Category name cannot be longer than {{ limit }} characters.',
        )]
        public string $name,

        #[Assert\NotNull(message: 'Category type is required.')]
        public CategoryType $type,
    ) {
    }
}
