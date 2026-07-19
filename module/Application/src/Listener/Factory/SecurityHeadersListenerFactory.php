<?php

declare(strict_types=1);

namespace Application\Listener\Factory;

use Application\Listener\SecurityHeadersListener;
use Psr\Container\ContainerInterface;

final class SecurityHeadersListenerFactory
{
    public function __invoke(ContainerInterface $container): SecurityHeadersListener
    {
        $config = $container->get('config');

        return new SecurityHeadersListener($config['security_headers'] ?? []);
    }
}
