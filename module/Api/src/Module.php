<?php

declare(strict_types=1);

namespace Api;

use Api\Middleware\CorsListener;
use Api\Middleware\JwtAuthListener;
use Laminas\Mvc\MvcEvent;

class Module
{
    public function getConfig(): array
    {
        /** @var array $config */
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
    }

    public function onBootstrap(MvcEvent $event): void
    {
        $services = $event->getApplication()->getServiceManager();
        $eventManager = $event->getApplication()->getEventManager();

        // Anexados de forma preguiçosa: só constroem CorsListener/JwtAuthListener
        // (e, por tabela, o JwtService — que exige jwt.secret configurado) quando
        // a rota casada realmente começa com "api-". Assim, o dashboard web, o
        // login e páginas de erro continuam funcionando mesmo sem jwt.secret
        // configurado; só a API exige.
        $isApiRoute = static function (MvcEvent $e): bool {
            $routeMatch = $e->getRouteMatch();
            return $routeMatch !== null && str_starts_with((string) $routeMatch->getMatchedRouteName(), 'api-');
        };

        $eventManager->attach(MvcEvent::EVENT_ROUTE, static function (MvcEvent $e) use ($services, $isApiRoute) {
            if (! $isApiRoute($e)) {
                return null;
            }

            return $services->get(CorsListener::class)($e);
        }, -90);

        $eventManager->attach(MvcEvent::EVENT_ROUTE, static function (MvcEvent $e) use ($services, $isApiRoute) {
            if (! $isApiRoute($e)) {
                return null;
            }

            return $services->get(JwtAuthListener::class)($e);
        }, -95);
    }
}
