<?php

declare(strict_types=1);

namespace Pessoa\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Pessoa\Repository\PessoaRepository;

/**
 * Exemplo completo de cadastro: usado tanto pelo CRUD do dashboard web
 * (Pessoa\Controller\PessoaController) quanto pela API consumida pelo app
 * (Api\Controller\PessoaController) — mesma Entity, duas portas de entrada.
 */
#[ORM\Entity(repositoryClass: PessoaRepository::class)]
#[ORM\Table(name: 'pessoa')]
class Pessoa
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 191)]
    private string $nome;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    private string $documento;

    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $telefone = null;

    // Só o NOME do arquivo (gerado pelo servidor) fica salvo — nunca o nome
    // original enviado pelo cliente. Ver Pessoa\Service\PessoaService::salvarFoto().
    #[ORM\Column(name: 'foto_path', type: 'string', length: 255, nullable: true)]
    private ?string $fotoPath = null;

    #[ORM\Column(name: 'criado_em', type: 'datetime_immutable')]
    private DateTimeImmutable $criadoEm;

    #[ORM\Column(name: 'atualizado_em', type: 'datetime_immutable')]
    private DateTimeImmutable $atualizadoEm;

    public function __construct(string $nome, string $documento)
    {
        $this->nome = $nome;
        $this->documento = $documento;
        $this->criadoEm = new DateTimeImmutable();
        $this->atualizadoEm = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
        $this->tocar();
    }

    public function getDocumento(): string
    {
        return $this->documento;
    }

    public function setDocumento(string $documento): void
    {
        $this->documento = $documento;
        $this->tocar();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
        $this->tocar();
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function setTelefone(?string $telefone): void
    {
        $this->telefone = $telefone;
        $this->tocar();
    }

    public function getFotoPath(): ?string
    {
        return $this->fotoPath;
    }

    public function setFotoPath(?string $fotoPath): void
    {
        $this->fotoPath = $fotoPath;
        $this->tocar();
    }

    private function tocar(): void
    {
        $this->atualizadoEm = new DateTimeImmutable();
    }

    /**
     * Representação pública — o que o dashboard e a API devolvem.
     * `foto_url` é relativa à origem do servidor (o app precisa prefixar
     * com a mesma base URL da API pra montar a URL completa da imagem).
     *
     * @return array{id: int|null, nome: string, documento: string, email: string|null, telefone: string|null, foto_url: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'documento' => $this->documento,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'foto_url' => $this->fotoPath !== null ? '/uploads/pessoas/' . $this->fotoPath : null,
        ];
    }
}
