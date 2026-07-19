<?php

declare(strict_types=1);

namespace Api\Controller\Factory;

use Api\Controller\AuthController;
use Api\Service\JwtService;
use Api\Service\RefreshTokenService;
use Auth\Service\DoctrineAuthAdapter;
use Auth\Service\LoginThrottleService;
use Psr\Container\ContainerInterface;

final class AuthControllerFactory
{
    public function __invoke(ContainerInterface $container): AuthController
    {
        return new AuthController(
            $container->get(DoctrineAuthAdapter::class),
            $container->get(LoginThrottleService::class),
            $container->get(JwtService::class),
            $container->get(RefreshTokenService::class),
        );
    }
}
