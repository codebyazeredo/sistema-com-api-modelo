<?php

declare(strict_types=1);

namespace Modelo\Service;

use Doctrine\ORM\EntityManagerInterface;
use Modelo\Entity\ModeloItem;
use Modelo\Repository\ModeloItemRepository;

/**
 * IMPORTANTE: nenhum método público devolve a Entity `ModeloItem` — só
 * `array` (via `ModeloItem::toArray()`). O Controller nunca deve
 * receber/manipular a Entity diretamente (mesmo princípio de
 * Pessoa\Service\PessoaService — ver o comentário lá pro porquê).
 */
final class ModeloItemService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** @return list<array{id: int|null, titulo: string, descricao: string|null}> */
    public function listarTodos(): array
    {
        /** @var ModeloItemRepository $repositorio */
        $repositorio = $this->entityManager->getRepository(ModeloItem::class);

        return array_map(
            static fn (ModeloItem $item): array => $item->toArray(),
            $repositorio->listarTodos(),
        );
    }

    /** @return array{id: int|null, titulo: string, descricao: string|null}|null */
    public function encontrar(int $id): ?array
    {
        return $this->entityManager->find(ModeloItem::class, $id)?->toArray();
    }

    /** @return array{id: int|null, titulo: string, descricao: string|null} */
    public function criar(string $titulo, ?string $descricao): array
    {
        $item = new ModeloItem($titulo, $descricao);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item->toArray();
    }

    /**
     * @return array{id: int|null, titulo: string, descricao: string|null}|null
     *         null quando `$id` não existe.
     */
    public function atualizar(int $id, string $titulo, ?string $descricao): ?array
    {
        $item = $this->entityManager->find(ModeloItem::class, $id);
        if ($item === null) {
            return null;
        }

        $item->setTitulo($titulo);
        $item->setDescricao($descricao);
        $this->entityManager->flush();

        return $item->toArray();
    }

    /** @return bool false quando `$id` não existe (nada a remover). */
    public function remover(int $id): bool
    {
        $item = $this->entityManager->find(ModeloItem::class, $id);
        if ($item === null) {
            return false;
        }

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return true;
    }
}
