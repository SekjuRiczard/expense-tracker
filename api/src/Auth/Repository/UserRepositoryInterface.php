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

namespace App\Auth\Repository;

use App\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

interface UserRepositoryInterface {
    public function save(User $user): void;
    public function findOneByEmail(string $email): ?User;
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void;

}