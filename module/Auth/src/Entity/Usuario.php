<?php

declare(strict_types=1);

namespace Auth\Entity;

use Auth\Repository\UsuarioRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Usuário genérico (um único tipo/perfil) — o mesmo conceito do `user` que
 * o app-modelo (Expo) espera de volta em POST /auth/login.
 */
#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\Table(name: 'usuario')]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 191, unique: true)]
    private string $login;

    #[ORM\Column(type: 'string', length: 191, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $senha;

    #[ORM\Column(type: 'string', length: 191)]
    private string $nome;

    #[ORM\Column(type: 'string', length: 30, options: ['default' => 'user'])]
    private string $role = 'user';

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $ativo = true;

    #[ORM\Column(name: 'criado_em', type: 'datetime_immutable')]
    private DateTimeImmutable $criadoEm;

    public function __construct(string $login, string $email, string $senhaHash, string $nome)
    {
        $this->login = mb_strtolower(trim($login));
        $this->email = mb_strtolower(trim($email));
        $this->senha = $senhaHash;
        $this->nome = $nome;
        $this->criadoEm = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function setSenha(string $senhaHash): void
    {
        $this->senha = $senhaHash;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): void
    {
        $this->ativo = $ativo;
    }

    /**
     * Representação pública do usuário — vira o identity da sessão web e o
     * `user` devolvido pela API (nunca inclui a senha).
     *
     * @return array{id: int|null, login: string, email: string, nome: string, role: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->email,
            'nome' => $this->nome,
            'role' => $this->role,
        ];
    }
}
