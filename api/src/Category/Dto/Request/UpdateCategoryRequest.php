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

final readonly class UpdateCategoryRequest
{
    public function __construct(
        #[Assert\Length(
            min: 1,
            max: 50,
            minMessage: 'Category name cannot be blank.',
            maxMessage: 'Category name cannot be longer than {{ limit }} characters.',
        )]
        public ?string $name = null,

        public ?CategoryType $type = null,
    ) {
    }
}
