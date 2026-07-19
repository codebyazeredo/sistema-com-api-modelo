<?php

declare(strict_types=1);

namespace Auth;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Laminas\Authentication\AuthenticationService;
use Laminas\Router\Http\Literal;

return [
    'router' => [
        'routes' => [
            'login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\DoctrineAuthAdapter::class => Service\Factory\DoctrineAuthAdapterFactory::class,
            Service\LoginThrottleService::class => Service\Factory\LoginThrottleServiceFactory::class,
            AuthenticationService::class => Service\Factory\AuthenticationServiceFactory::class,
        ],
    ],

    // Bloqueio de força-bruta (Service\LoginThrottleService), reaproveitado
    // também pelo login da API (módulo Api).
    'login_throttle' => [
        'max_attempts' => 5,
        'window_minutes' => 15,
    ],

    'doctrine' => [
        'driver' => [
            'auth_entities' => [
                'class' => AttributeDriver::class,
                'paths' => [__DIR__ . '/../src/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    'Auth\Entity' => 'auth_entities',
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'layout/login' => __DIR__ . '/../view/layout/login.phtml',
            'auth/auth/login' => __DIR__ . '/../view/auth/auth/login.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
