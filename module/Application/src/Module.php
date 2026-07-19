<?php

declare(strict_types=1);

namespace Application;

use Application\Listener\AuthGuardListener;
use Application\Listener\SecurityHeadersListener;
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
        $application = $event->getApplication();
        $services = $application->getServiceManager();
        $eventManager = $application->getEventManager();

        // Cabeçalhos de segurança em toda resposta, inclusive erro/404.
        $eventManager->attach(MvcEvent::EVENT_FINISH, $services->get(SecurityHeadersListener::class));

        // Guarda de sessão do dashboard web — depois do roteamento, antes do
        // controlador ser executado.
        $eventManager->attach(MvcEvent::EVENT_ROUTE, $services->get(AuthGuardListener::class), -100);
    }
}
