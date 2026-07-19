<?php

declare(strict_types=1);

namespace Auth\Controller\Factory;

use Auth\Controller\AuthController;
use Auth\Form\LoginForm;
use Auth\Service\DoctrineAuthAdapter;
use Auth\Service\LoginThrottleService;
use Laminas\Authentication\AuthenticationService;
use Laminas\Session\SessionManager;
use Psr\Container\ContainerInterface;

final class AuthControllerFactory
{
    public function __invoke(ContainerInterface $container): AuthController
    {
        return new AuthController(
            $container->get(AuthenticationService::class),
            $container->get(DoctrineAuthAdapter::class),
            $container->get(LoginThrottleService::class),
            $container->get(SessionManager::class),
            new LoginForm(),
        );
    }
}
