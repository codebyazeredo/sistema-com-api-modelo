<?php

declare(strict_types=1);

namespace Pessoa\Service\Factory;

use Pessoa\Service\PessoaService;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class PessoaServiceFactory
{
    public function __invoke(ContainerInterface $container): PessoaService
    {
        $config = $container->get('config');
        $uploadsDir = $config['pessoa']['uploads_dir'] ?? null;

        if (! is_string($uploadsDir) || $uploadsDir === '') {
            throw new RuntimeException("config 'pessoa.uploads_dir' não configurado.");
        }

        return new PessoaService(
            $container->get('doctrine.entitymanager.orm_default'),
            $uploadsDir,
        );
    }
}
