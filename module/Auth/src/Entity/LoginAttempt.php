<?php

declare(strict_types=1);

namespace Auth\Entity;

use Auth\Repository\LoginAttemptRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Registro de CADA tentativa de login (sucesso ou falha), usado pelo
 * LoginThrottleService para bloquear força-bruta. Guardar o histórico (em
 * vez de só um contador) evita condição de corrida e dá trilha de auditoria.
 */
#[ORM\Entity(repositoryClass: LoginAttemptRepository::class)]
#[ORM\Table(name: 'login_attempt')]
#[ORM\Index(columns: ['login', 'criado_em'], name: 'idx_login_attempt_login_criado')]
class LoginAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 191)]
    private string $login;

    #[ORM\Column(type: 'string', length: 45)]
    private string $ip;

    #[ORM\Column(type: 'boolean')]
    private bool $sucesso;

    #[ORM\Column(name: 'criado_em', type: 'datetime_immutable')]
    private DateTimeImmutable $criadoEm;

    public function __construct(string $login, string $ip, bool $sucesso)
    {
        $this->login = mb_strtolower(trim($login));
        $this->ip = $ip;
        $this->sucesso = $sucesso;
        $this->criadoEm = new DateTimeImmutable();
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function isSucesso(): bool
    {
        return $this->sucesso;
    }
}
