<?php

declare(strict_types=1);

namespace Api\Repository;

use Api\Entity\RefreshToken;
use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<RefreshToken> */
class RefreshTokenRepository extends EntityRepository
{
    public function encontrarPorHash(string $tokenHash): ?RefreshToken
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }

    /**
     * Revoga por ID do usuário via `IDENTITY()` — evita ter que carregar a
     * Entity `Usuario` só para filtrar a query.
     */
    public function revogarTodosPorUsuarioId(int $usuarioId): void
    {
        $this->createQueryBuilder('rt')
            ->update()
            ->set('rt.revokedAt', ':agora')
            ->andWhere('IDENTITY(rt.usuario) = :usuarioId')
            ->andWhere('rt.revokedAt IS NULL')
            ->setParameter('agora', new DateTimeImmutable())
            ->setParameter('usuarioId', $usuarioId)
            ->getQuery()
            ->execute();
    }
}
