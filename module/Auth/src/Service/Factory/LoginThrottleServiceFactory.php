<?php

declare(strict_types=1);

namespace Auth\Service\Factory;

use Auth\Service\LoginThrottleService;
use Psr\Container\ContainerInterface;

final class LoginThrottleServiceFactory
{
    public function __invoke(ContainerInterface $container): LoginThrottleService
    {
        $config = $container->get('config')['login_throttle'] ?? [];

        return new LoginThrottleService(
            $container->get('doctrine.entitymanager.orm_default'),
            $config['max_attempts'] ?? 5,
            $config['window_minutes'] ?? 15,
        );
    }
}
