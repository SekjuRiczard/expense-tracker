<?php

declare(strict_types=1);

namespace App\Auth\Repository;

use App\Entity\PasswordResetCode;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

final class PasswordResetCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetCode::class);
    }

    public function save(PasswordResetCode $passwordResetCode): void
    {
        $this->getEntityManager()->persist($passwordResetCode);
        $this->getEntityManager()->flush();
    }

    public function findActiveCode(User $user, string $codeHash): ?PasswordResetCode
    {
        /** @var ?PasswordResetCode $passwordResetCode */
        $passwordResetCode = $this->createQueryBuilder('prc')
            ->andWhere('IDENTITY(prc.user) = :userId')
            ->andWhere('prc.codeHash = :codeHash')
            ->andWhere('prc.usedAt IS NULL')
            ->setParameter('userId', $user->getId(), UuidType::NAME)
            ->setParameter('codeHash', $codeHash)
            ->orderBy('prc.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $passwordResetCode;
    }
}