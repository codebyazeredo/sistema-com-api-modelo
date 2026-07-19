<?php

declare(strict_types=1);

namespace Auth\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<\Auth\Entity\LoginAttempt> */
class LoginAttemptRepository extends EntityRepository
{
    public function contarFalhasRecentes(string $login, DateTimeImmutable $desde): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.login = :login')
            ->andWhere('a.sucesso = false')
            ->andWhere('a.criadoEm >= :desde')
            ->setParameter('login', mb_strtolower(trim($login)))
            ->setParameter('desde', $desde)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
