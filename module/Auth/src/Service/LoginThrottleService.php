<?php

declare(strict_types=1);

namespace Auth\Service;

use Auth\Entity\LoginAttempt;
use Auth\Repository\LoginAttemptRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Bloqueio simples contra força-bruta: conta falhas recentes por login numa
 * janela de tempo. Usado tanto pelo login web (Auth\Controller\AuthController)
 * quanto pelo login da API (Api\Controller\AuthController).
 */
final class LoginThrottleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly int $maxAttempts = 5,
        private readonly int $windowMinutes = 15,
    ) {
    }

    public function estaBloqueado(string $login): bool
    {
        return $this->falhasRecentes($login) >= $this->maxAttempts;
    }

    public function registrarSucesso(string $login, string $ip): void
    {
        $this->registrar($login, $ip, true);
    }

    public function registrarFalha(string $login, string $ip): void
    {
        $this->registrar($login, $ip, false);
    }

    private function registrar(string $login, string $ip, bool $sucesso): void
    {
        $this->entityManager->persist(new LoginAttempt($login, $ip, $sucesso));
        $this->entityManager->flush();
    }

    private function falhasRecentes(string $login): int
    {
        /** @var LoginAttemptRepository $repositorio */
        $repositorio = $this->entityManager->getRepository(LoginAttempt::class);
        $desde = new DateTimeImmutable(sprintf('-%d minutes', $this->windowMinutes));

        return $repositorio->contarFalhasRecentes($login, $desde);
    }
}
