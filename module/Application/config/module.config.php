<?php

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\Literal;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => Controller\DashboardController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\DashboardController::class => Controller\Factory\DashboardControllerFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\PasswordHasher::class => InvokableFactory::class,
            Listener\SecurityHeadersListener::class => Listener\Factory\SecurityHeadersListenerFactory::class,
            Listener\AuthGuardListener::class => Listener\Factory\AuthGuardListenerFactory::class,
        ],
    ],

    'view_helpers' => [
        'factories' => [
            View\Helper\Identity::class => View\Helper\Factory\IdentityFactory::class,
        ],
        'aliases' => [
            'identity' => View\Helper\Identity::class,
        ],
    ],

    // Rotas do dashboard web que NÃO exigem sessão autenticada (o guard em
    // Listener\AuthGuardListener libera estas + tudo que começa com "api-").
    'auth' => [
        'public_routes' => ['login'],
        'login_route' => 'login',
    ],

    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/dashboard/index' => __DIR__ . '/../view/application/dashboard/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
