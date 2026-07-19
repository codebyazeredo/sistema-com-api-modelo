<?php

declare(strict_types=1);

namespace Api\Entity;

use Api\Repository\RefreshTokenRepository;
use Auth\Entity\Usuario;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Refresh token opaco: só o HASH (sha256) fica aqui, nunca o valor cru — ver
 * Api\Service\RefreshTokenService.
 */
#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_token')]
#[ORM\Index(columns: ['token_hash'], name: 'idx_refresh_token_hash')]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Usuario $usuario;

    #[ORM\Column(name: 'token_hash', type: 'string', length: 64, unique: true)]
    private string $tokenHash;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'revoked_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $revokedAt = null;

    #[ORM\Column(name: 'criado_em', type: 'datetime_immutable')]
    private DateTimeImmutable $criadoEm;

    public function __construct(Usuario $usuario, string $tokenHash, DateTimeImmutable $expiresAt)
    {
        $this->usuario = $usuario;
        $this->tokenHash = $tokenHash;
        $this->expiresAt = $expiresAt;
        $this->criadoEm = new DateTimeImmutable();
    }

    public function getUsuario(): Usuario
    {
        return $this->usuario;
    }

    public function revogar(): void
    {
        $this->revokedAt = new DateTimeImmutable();
    }

    public function estaValido(): bool
    {
        return $this->revokedAt === null && $this->expiresAt > new DateTimeImmutable();
    }

    public function estaRevogado(): bool
    {
        return $this->revokedAt !== null;
    }
}
