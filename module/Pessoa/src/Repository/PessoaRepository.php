<?php

declare(strict_types=1);

namespace Pessoa\Repository;

use Doctrine\ORM\EntityRepository;
use Pessoa\Entity\Pessoa;

/** @extends EntityRepository<Pessoa> */
class PessoaRepository extends EntityRepository
{
    /** @return list<Pessoa> */
    public function listarTodos(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.nome', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
