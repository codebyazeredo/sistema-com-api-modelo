<?php

declare(strict_types=1);

namespace Modelo\Repository;

use Doctrine\ORM\EntityRepository;
use Modelo\Entity\ModeloItem;

/** @extends EntityRepository<ModeloItem> */
class ModeloItemRepository extends EntityRepository
{
    /** @return list<ModeloItem> */
    public function listarTodos(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.criadoEm', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
