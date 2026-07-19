<?php

declare(strict_types=1);

namespace Api;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'api-pessoas' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/pessoas[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\PessoaApiController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'api-auth-login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/auth/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'api-auth-refresh' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/auth/refresh',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'refresh',
                    ],
                ],
            ],
            'api-auth-logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/auth/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'api-me' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/me',
                    'defaults' => [
                        'controller' => Controller\MeController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
            Controller\MeController::class => Controller\Factory\MeControllerFactory::class,
            Controller\PessoaApiController::class => Controller\Factory\PessoaApiControllerFactory::class,
        ],
    ],

    // Sem isto, um JsonModel devolvido por qualquer controller da API cai no
    // renderizador padrão de .phtml (que não existe pra esses controllers) em
    // vez de virar JSON de verdade. Só afeta respostas que já são JsonModel —
    // as telas normais do dashboard (ViewModel) continuam intactas.
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\JwtService::class => Service\Factory\JwtServiceFactory::class,
            Service\RefreshTokenService::class => Service\Factory\RefreshTokenServiceFactory::class,
            Middleware\CorsListener::class => Middleware\Factory\CorsListenerFactory::class,
            Middleware\JwtAuthListener::class => Middleware\Factory\JwtAuthListenerFactory::class,
        ],
    ],

    // Rotas da API que NÃO exigem "Authorization: Bearer" (JwtAuthListener).
    // Tudo o mais que começar com "api-" exige token válido.
    'api' => [
        'public_routes' => ['api-auth-login', 'api-auth-refresh'],
    ],

    // Segredo do JWT SEMPRE em config/autoload/local.php (nunca aqui).
    'jwt' => [
        'issuer' => 'sistema-com-api-modelo',
        'access_token_ttl' => 60 * 60,           // 1h
        'refresh_token_ttl' => 60 * 60 * 24 * 30, // 30 dias
    ],

    // Allow-list de CORS por ambiente — real em config/autoload/local.php.
    'cors' => [
        'allowed_origins' => [],
    ],

    'doctrine' => [
        'driver' => [
            'api_entities' => [
                'class' => AttributeDriver::class,
                'paths' => [__DIR__ . '/../src/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    'Api\Entity' => 'api_entities',
                ],
            ],
        ],
    ],
];
