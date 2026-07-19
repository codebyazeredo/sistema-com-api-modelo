<?php

declare(strict_types=1);

namespace Application\Service;

/**
 * Hash de senha com Argon2id (nunca texto puro, nunca MD5/SHA1/hash rápido).
 * Sem estado/dependências — pode ser instanciado direto (InvokableFactory).
 */
final class PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_ARGON2ID);
    }

    public function verify(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }

    /** Indica se o hash foi gerado com parâmetros antigos e deve ser regravado. */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }
}
