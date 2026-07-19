<?php

declare(strict_types=1);

namespace Modelo\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Modelo\Repository\ModeloItemRepository;

/**
 * Entidade de EXEMPLO — mostra o padrão (Entity + Repository + Form +
 * Controller + View) a ser copiado ao adicionar a regra de negócio real.
 */
#[ORM\Entity(repositoryClass: ModeloItemRepository::class)]
#[ORM\Table(name: 'modelo_item')]
class ModeloItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 191)]
    private string $titulo;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descricao = null;

    #[ORM\Column(name: 'criado_em', type: 'datetime_immutable')]
    private DateTimeImmutable $criadoEm;

    public function __construct(string $titulo, ?string $descricao = null)
    {
        $this->titulo = $titulo;
        $this->descricao = $descricao;
        $this->criadoEm = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): void
    {
        $this->titulo = $titulo;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function getCriadoEm(): DateTimeImmutable
    {
        return $this->criadoEm;
    }

    /**
     * Representação pública — o Controller nunca deve receber esta Entity
     * diretamente, só o array daqui (ver Modelo\Service\ModeloItemService).
     *
     * @return array{id: int|null, titulo: string, descricao: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
        ];
    }
}
