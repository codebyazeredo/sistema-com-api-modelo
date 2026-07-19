<?php

declare(strict_types=1);

namespace Api\Middleware;

use Api\Service\JwtService;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;

/**
 * Guarda da API: toda rota "api-*" exige "Authorization: Bearer <token>"
 * válido, exceto as listadas em config('api')['public_routes'] (login/refresh).
 * Em caso de sucesso, injeta os claims decodificados em jwt_claims para os
 * controllers lerem via $this->params()->fromRoute('jwt_claims').
 */
final class JwtAuthListener
{
    /** @param list<string> $publicRoutes */
    public function __construct(
        private readonly JwtService $jwt,
        private readonly array $publicRoutes,
    ) {
    }

    public function __invoke(MvcEvent $event): ?Response
    {
        $routeMatch = $event->getRouteMatch();
        if ($routeMatch === null) {
            return null;
        }

        $routeName = (string) $routeMatch->getMatchedRouteName();
        if (! str_starts_with($routeName, 'api-') || in_array($routeName, $this->publicRoutes, true)) {
            return null;
        }

        $request = $event->getRequest();
        $token = null;
        if ($request instanceof Request && $request->getHeaders()->has('Authorization')) {
            $header = (string) $request->getHeaders()->get('Authorization')->getFieldValue();
            if (str_starts_with($header, 'Bearer ')) {
                $token = substr($header, 7);
            }
        }

        $claims = $token !== null ? $this->jwt->validate($token) : null;
        if ($claims === null) {
            return $this->unauthorized($event);
        }

        $routeMatch->setParam('jwt_claims', $claims);

        return null;
    }

    private function unauthorized(MvcEvent $event): Response
    {
        $response = $event->getResponse();
        if (! $response instanceof Response) {
            $response = new Response();
        }
        $response->setStatusCode(401);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent((string) json_encode(['message' => 'Não autenticado.']));

        $event->setResponse($response);
        $event->stopPropagation(true);

        return $response;
    }
}
