<?php

declare(strict_types=1);

namespace Auth\Service;

use Auth\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Único ponto de leitura de `Usuario` pra quem só precisa dos dados
 * públicos (ex.: Api\Controller\MeController) — nunca devolve a Entity,
 * só `array` (via `Usuario::toArray()`). Controllers não devem importar
 * `Auth\Entity\Usuario` nem `EntityManagerInterface` pra isso.
 */
final class UsuarioService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** @return array{id:int,login:string,email:string,nome:string,role:string}|null */
    public function encontrarPorId(int $id): ?array
    {
        return $this->entityManager->find(Usuario::class, $id)?->toArray();
    }
}
