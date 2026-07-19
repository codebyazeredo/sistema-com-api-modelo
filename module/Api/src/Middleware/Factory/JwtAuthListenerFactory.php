<?php

declare(strict_types=1);

namespace Api\Middleware\Factory;

use Api\Middleware\JwtAuthListener;
use Api\Service\JwtService;
use Psr\Container\ContainerInterface;

final class JwtAuthListenerFactory
{
    public function __invoke(ContainerInterface $container): JwtAuthListener
    {
        $config = $container->get('config')['api'] ?? [];

        return new JwtAuthListener(
            $container->get(JwtService::class),
            $config['public_routes'] ?? [],
        );
    }
}
