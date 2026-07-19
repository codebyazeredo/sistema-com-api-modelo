<?php

declare(strict_types=1);

namespace Api\Service\Factory;

use Api\Service\RefreshTokenService;
use Psr\Container\ContainerInterface;

final class RefreshTokenServiceFactory
{
    public function __invoke(ContainerInterface $container): RefreshTokenService
    {
        $config = $container->get('config')['jwt'] ?? [];

        return new RefreshTokenService(
            $container->get('doctrine.entitymanager.orm_default'),
            (int) ($config['refresh_token_ttl'] ?? 60 * 60 * 24 * 30),
        );
    }
}
