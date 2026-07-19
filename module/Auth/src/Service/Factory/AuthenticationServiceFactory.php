<?php

declare(strict_types=1);

namespace Auth\Service\Factory;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\Session as SessionStorage;
use Psr\Container\ContainerInterface;

/**
 * Serviço de autenticação da sessão web (dashboard). A API (módulo Api) NÃO
 * usa isto — ela é stateless via JWT/Bearer.
 */
final class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationService
    {
        return new AuthenticationService(new SessionStorage('auth'));
    }
}
