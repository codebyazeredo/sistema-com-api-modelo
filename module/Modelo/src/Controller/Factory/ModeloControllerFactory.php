<?php

declare(strict_types=1);

namespace Modelo\Controller\Factory;

use Modelo\Controller\ModeloController;
use Psr\Container\ContainerInterface;

final class ModeloControllerFactory
{
    public function __invoke(ContainerInterface $container): ModeloController
    {
        return new ModeloController($container->get('doctrine.entitymanager.orm_default'));
    }
}
