<?php

declare(strict_types=1);

namespace Modelo\Service\Factory;

use Modelo\Service\ModeloItemService;
use Psr\Container\ContainerInterface;

final class ModeloItemServiceFactory
{
    public function __invoke(ContainerInterface $container): ModeloItemService
    {
        return new ModeloItemService($container->get('doctrine.entitymanager.orm_default'));
    }
}
