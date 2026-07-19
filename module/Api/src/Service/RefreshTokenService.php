<?php

declare(strict_types=1);

namespace Api\Service;

use Api\Entity\RefreshToken;
use Api\Repository\RefreshTokenRepository;
use Auth\Entity\Usuario;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

/**
 * Refresh token opaco (não é JWT): valor aleatório de 256 bits — só o hash
 * (sha256) fica no banco, o valor cru existe apenas na resposta ao cliente,
 * uma única vez. Rotativo: cada uso troca por um novo e revoga o antigo;
 * reapresentar um token já revogado é tratado como possível roubo e revoga
 * TODOS os tokens do usuário (força novo login em todos os dispositivos).
 *
 * IMPORTANTE: nenhum método PÚBLICO recebe ou devolve a Entity `Usuario` —
 * só `id` (int) na entrada e `array` (via `Usuario::toArray()`) na saída.
 * A Entity só é tocada internamente (`emitir()` é privado), porque o
 * Doctrine precisa dela pra montar a relação do `RefreshToken` — isso nunca
 * deve vazar pro Controller.
 */
final class RefreshTokenService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly int $ttlSeconds,
    ) {
    }

    /** @return array{token: string, expiresAt: DateTimeImmutable} */
    public function emitirParaUsuario(int $usuarioId): array
    {
        $usuario = $this->entityManager->find(Usuario::class, $usuarioId);
        if ($usuario === null) {
            throw new RuntimeException('Usuário não encontrado.');
        }

        return $this->emitir($usuario);
    }

    /**
     * @return array{user: array{id:int,login:string,email:string,nome:string,role:string}, token: string, expiresAt: DateTimeImmutable}|null
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
            $repositorio->revogarTodosPorUsuarioId((int) $entidade->getUsuario()->getId());
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

        return ['user' => $usuario->toArray(), 'token' => $novo['token'], 'expiresAt' => $novo['expiresAt']];
    }

    public function revogarTodosDoUsuarioId(int $usuarioId): void
    {
        /** @var RefreshTokenRepository $repositorio */
        $repositorio = $this->entityManager->getRepository(RefreshToken::class);
        $repositorio->revogarTodosPorUsuarioId($usuarioId);
        $this->entityManager->flush();
    }

    /** @return array{token: string, expiresAt: DateTimeImmutable} */
    private function emitir(Usuario $usuario): array
    {
        $tokenBruto = bin2hex(random_bytes(32));
        $expiraEm = new DateTimeImmutable(sprintf('+%d seconds', $this->ttlSeconds));

        $entidade = new RefreshToken($usuario, hash('sha256', $tokenBruto), $expiraEm);
        $this->entityManager->persist($entidade);
        $this->entityManager->flush();

        return ['token' => $tokenBruto, 'expiresAt' => $expiraEm];
    }
}
