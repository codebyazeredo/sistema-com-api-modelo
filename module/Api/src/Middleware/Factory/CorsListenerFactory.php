<?php

declare(strict_types=1);

namespace Api\Middleware\Factory;

use Api\Middleware\CorsListener;
use Psr\Container\ContainerInterface;

final class CorsListenerFactory
{
    public function __invoke(ContainerInterface $container): CorsListener
    {
        $config = $container->get('config')['cors'] ?? [];

        return new CorsListener($config['allowed_origins'] ?? []);
    }
}
