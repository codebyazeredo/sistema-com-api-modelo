<?php

declare(strict_types=1);

namespace Api\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

/**
 * Token de acesso da API (curta duração, HS256). O refresh token (opaco,
 * rotativo) é responsabilidade de Api\Service\RefreshTokenService.
 */
final class JwtService
{
    public function __construct(
        private readonly string $secret,
        private readonly string $issuer,
        private readonly int $accessTokenTtl,
    ) {
    }

    /** @param array<string, mixed> $claims */
    public function issue(array $claims): string
    {
        $now = time();
        $payload = array_merge($claims, [
            'iss' => $this->issuer,
            'iat' => $now,
            'exp' => $now + $this->accessTokenTtl,
        ]);

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /** @return array<string, mixed>|null */
    public function validate(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));

            return (array) $decoded;
        } catch (Throwable) {
            // Assinatura inválida, expirado, malformado etc. — nunca vazar o
            // motivo exato pro cliente, só null (== não autenticado).
            return null;
        }
    }
}
