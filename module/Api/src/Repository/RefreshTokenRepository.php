<?php

declare(strict_types=1);

namespace Api\Repository;

use Api\Entity\RefreshToken;
use Auth\Entity\Usuario;
use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<RefreshToken> */
class RefreshTokenRepository extends EntityRepository
{
    public function encontrarPorHash(string $tokenHash): ?RefreshToken
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }

    public function revogarTodosDoUsuario(Usuario $usuario): void
    {
        $this->createQueryBuilder('rt')
            ->update()
            ->set('rt.revokedAt', ':agora')
            ->andWhere('rt.usuario = :usuario')
            ->andWhere('rt.revokedAt IS NULL')
            ->setParameter('agora', new DateTimeImmutable())
            ->setParameter('usuario', $usuario)
            ->getQuery()
            ->execute();
    }
}
