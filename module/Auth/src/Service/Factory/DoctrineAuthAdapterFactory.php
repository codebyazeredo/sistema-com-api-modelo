<?php

declare(strict_types=1);

namespace Auth\Service\Factory;

use Application\Service\PasswordHasher;
use Auth\Service\DoctrineAuthAdapter;
use Psr\Container\ContainerInterface;

final class DoctrineAuthAdapterFactory
{
    public function __invoke(ContainerInterface $container): DoctrineAuthAdapter
    {
        return new DoctrineAuthAdapter(
            $container->get('doctrine.entitymanager.orm_default'),
            $container->get(PasswordHasher::class),
        );
    }
}
