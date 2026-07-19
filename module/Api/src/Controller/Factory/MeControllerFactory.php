<?php

declare(strict_types=1);

namespace Api\Controller\Factory;

use Api\Controller\MeController;
use Psr\Container\ContainerInterface;

final class MeControllerFactory
{
    public function __invoke(ContainerInterface $container): MeController
    {
        return new MeController($container->get('doctrine.entitymanager.orm_default'));
    }
}
