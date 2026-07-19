<?php

declare(strict_types=1);

namespace Api\Controller\Factory;

use Api\Controller\PessoaApiController;
use Pessoa\Service\PessoaService;
use Psr\Container\ContainerInterface;

final class PessoaApiControllerFactory
{
    public function __invoke(ContainerInterface $container): PessoaApiController
    {
        return new PessoaApiController($container->get(PessoaService::class));
    }
}
