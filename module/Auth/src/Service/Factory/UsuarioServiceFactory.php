<?php

declare(strict_types=1);

namespace Auth\Service\Factory;

use Auth\Service\UsuarioService;
use Psr\Container\ContainerInterface;

final class UsuarioServiceFactory
{
    public function __invoke(ContainerInterface $container): UsuarioService
    {
        return new UsuarioService($container->get('doctrine.entitymanager.orm_default'));
    }
}
