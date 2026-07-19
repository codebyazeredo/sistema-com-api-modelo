<?php

declare(strict_types=1);

namespace Application\Listener\Factory;

use Application\Listener\AuthGuardListener;
use Laminas\Authentication\AuthenticationService;
use Psr\Container\ContainerInterface;

final class AuthGuardListenerFactory
{
    public function __invoke(ContainerInterface $container): AuthGuardListener
    {
        $config = $container->get('config');
        $authConfig = $config['auth'] ?? [];

        return new AuthGuardListener(
            $container->get(AuthenticationService::class),
            $authConfig['public_routes'] ?? [],
            $authConfig['login_route'] ?? 'login',
        );
    }
}
