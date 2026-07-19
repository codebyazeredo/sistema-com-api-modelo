<?php

declare(strict_types=1);

namespace Application\Controller\Factory;

use Application\Controller\DashboardController;
use Laminas\Authentication\AuthenticationService;
use Psr\Container\ContainerInterface;

final class DashboardControllerFactory
{
    public function __invoke(ContainerInterface $container): DashboardController
    {
        return new DashboardController($container->get(AuthenticationService::class));
    }
}
