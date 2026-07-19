<?php

declare(strict_types=1);

namespace Auth\Repository;

use Auth\Entity\Usuario;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<Usuario> */
class UsuarioRepository extends EntityRepository
{
    public function encontrarAtivoPorLoginOuEmail(string $login): ?Usuario
    {
        $login = mb_strtolower(trim($login));

        return $this->createQueryBuilder('u')
            ->andWhere('u.login = :login OR u.email = :login')
            ->andWhere('u.ativo = true')
            ->setParameter('login', $login)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
