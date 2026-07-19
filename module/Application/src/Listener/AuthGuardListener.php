<?php

declare(strict_types=1);

namespace Application\Listener;

use Laminas\Authentication\AuthenticationService;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;

/**
 * Guarda de sessão do DASHBOARD WEB: qualquer rota não listada em
 * `public_routes` e que não pertença à API (prefixo "api-", cuidada pelo
 * JwtAuthListener do módulo Api) exige usuário autenticado por sessão —
 * caso contrário redireciona para o login.
 */
final class AuthGuardListener
{
    /** @param list<string> $publicRoutes */
    public function __construct(
        private readonly AuthenticationService $auth,
        private readonly array $publicRoutes,
        private readonly string $loginRouteName,
    ) {
    }

    public function __invoke(MvcEvent $event): ?Response
    {
        $routeMatch = $event->getRouteMatch();
        if ($routeMatch === null) {
            return null;
        }

        $routeName = (string) $routeMatch->getMatchedRouteName();
        if ($routeName === '' || str_starts_with($routeName, 'api-') || in_array($routeName, $this->publicRoutes, true)) {
            return null;
        }

        if ($this->auth->hasIdentity()) {
            return null;
        }

        $router = $event->getRouter();
        $loginUrl = $router->assemble([], ['name' => $this->loginRouteName]);

        $response = $event->getResponse();
        if (! $response instanceof Response) {
            $response = new Response();
        }
        $response->getHeaders()->addHeaderLine('Location', $loginUrl);
        $response->setStatusCode(302);

        $event->setResponse($response);
        $event->stopPropagation(true);

        return $response;
    }
}
