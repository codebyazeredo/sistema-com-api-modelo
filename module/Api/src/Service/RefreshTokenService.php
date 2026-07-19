<?php

declare(strict_types=1);

namespace Api\Service;

use Api\Entity\RefreshToken;
use Api\Repository\RefreshTokenRepository;
use Auth\Entity\Usuario;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Refresh token opaco (não é JWT): valor aleatório de 256 bits — só o hash
 * (sha256) fica no banco, o valor cru existe apenas na resposta ao cliente,
 * uma única vez. Rotativo: cada uso troca por um novo e revoga o antigo;
 * reapresentar um token já revogado é tratado como possível roubo e revoga
 * TODOS os tokens do usuário (força novo login em todos os dispositivos).
 */
final class RefreshTokenService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly int $ttlSeconds,
    ) {
    }

    /** @return array{token: string, expiresAt: DateTimeImmutable} */
    public function emitir(Usuario $usuario): array
    {
        $tokenBruto = bin2hex(random_bytes(32));
        $expiraEm = new DateTimeImmutable(sprintf('+%d seconds', $this->ttlSeconds));

        $entidade = new RefreshToken($usuario, hash('sha256', $tokenBruto), $expiraEm);
        $this->entityManager->persist($entidade);
        $this->entityManager->flush();

        return ['token' => $tokenBruto, 'expiresAt' => $expiraEm];
    }

    /**
     * @return array{usuario: Usuario, token: string, expiresAt: DateTimeImmutable}|null
     *         null quando o token é inválido, expirado, ou reuso de um token
     *         já revogado (nesse caso todos os tokens do usuário são revogados).
     */
    public function rotacionar(string $tokenBruto): ?array
    {
        /** @var RefreshTokenRepository $repositorio */
        $repositorio = $this->entityManager->getRepository(RefreshToken::class);
        $entidade = $repositorio->encontrarPorHash(hash('sha256', $tokenBruto));

        if ($entidade === null) {
            return null;
        }

        if ($entidade->estaRevogado()) {
            $repositorio->revogarTodosDoUsuario($entidade->getUsuario());
            $this->entityManager->flush();

            return null;
        }

        if (! $entidade->estaValido()) {
            return null;
        }

        $usuario = $entidade->getUsuario();
        $entidade->revogar();
        $novo = $this->emitir($usuario);
        $this->entityManager->flush();

        return ['usuario' => $usuario, 'token' => $novo['token'], 'expiresAt' => $novo['expiresAt']];
    }

    public function revogarTodosDoUsuario(Usuario $usuario): void
    {
        /** @var RefreshTokenRepository $repositorio */
        $repositorio = $this->entityManager->getRepository(RefreshToken::class);
        $repositorio->revogarTodosDoUsuario($usuario);
        $this->entityManager->flush();
    }
}
