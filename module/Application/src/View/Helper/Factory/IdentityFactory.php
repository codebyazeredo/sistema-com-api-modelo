<?php

declare(strict_types=1);

namespace Application\View\Helper\Factory;

use Application\View\Helper\Identity;
use Laminas\Authentication\AuthenticationService;
use Psr\Container\ContainerInterface;

final class IdentityFactory
{
    public function __invoke(ContainerInterface $container): Identity
    {
        return new Identity($container->get(AuthenticationService::class));
    }
}
