<?php

declare(strict_types=1);

namespace Api\Service\Factory;

use Api\Service\JwtService;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class JwtServiceFactory
{
    public function __invoke(ContainerInterface $container): JwtService
    {
        $config = $container->get('config')['jwt'] ?? [];
        $secret = (string) ($config['secret'] ?? '');

        if (strlen($secret) < 32) {
            throw new RuntimeException(
                'jwt.secret ausente ou muito curto em config/autoload/local.php. Gere um com: '
                . 'php -r "echo bin2hex(random_bytes(32));"'
            );
        }

        return new JwtService(
            $secret,
            (string) ($config['issuer'] ?? 'sistema-com-api-modelo'),
            (int) ($config['access_token_ttl'] ?? 3600),
        );
    }
}
