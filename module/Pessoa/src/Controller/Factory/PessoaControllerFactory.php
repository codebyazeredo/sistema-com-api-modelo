<?php

declare(strict_types=1);

namespace Pessoa\Controller\Factory;

use Pessoa\Controller\PessoaController;
use Pessoa\Service\PessoaService;
use Psr\Container\ContainerInterface;

final class PessoaControllerFactory
{
    public function __invoke(ContainerInterface $container): PessoaController
    {
        return new PessoaController($container->get(PessoaService::class));
    }
}
