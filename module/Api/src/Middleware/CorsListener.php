<?php

declare(strict_types=1);

namespace Api\Middleware;

use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;

/**
 * CORS por allow-list explícita (config/autoload/local.php: cors.allowed_origins)
 * — nunca reflete Origin arbitrária e nunca envia Allow-Credentials (a API
 * usa Bearer, não cookie, então não precisa de credentials cross-origin).
 * Apps mobile não enviam Origin, então não são afetados por CORS.
 */
final class CorsListener
{
    /** @param list<string> $allowedOrigins */
    public function __construct(private readonly array $allowedOrigins)
    {
    }

    public function __invoke(MvcEvent $event): ?Response
    {
        $routeMatch = $event->getRouteMatch();
        if ($routeMatch === null || ! str_starts_with((string) $routeMatch->getMatchedRouteName(), 'api-')) {
            return null;
        }

        $request = $event->getRequest();
        if (! $request instanceof Request) {
            return null;
        }

        $response = $event->getResponse();
        if (! $response instanceof Response) {
            return null;
        }

        if ($request->getHeaders()->has('Origin')) {
            $origin = (string) $request->getHeaders()->get('Origin')->getFieldValue();
            if (in_array($origin, $this->allowedOrigins, true)) {
                $response->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', $origin);
                $response->getHeaders()->addHeaderLine('Vary', 'Origin');
            }
        }

        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            $response->getHeaders()->addHeaderLine('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->getHeaders()->addHeaderLine('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $response->getHeaders()->addHeaderLine('Access-Control-Max-Age', '86400');
            $response->setStatusCode(204);
            $event->setResponse($response);
            $event->stopPropagation(true);

            return $response;
        }

        return null;
    }
}
