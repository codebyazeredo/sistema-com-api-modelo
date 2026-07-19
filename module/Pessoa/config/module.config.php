<?php

declare(strict_types=1);

namespace Pessoa;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'pessoa' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/pessoa[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\PessoaController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\PessoaController::class => Controller\Factory\PessoaControllerFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\PessoaService::class => Service\Factory\PessoaServiceFactory::class,
        ],
    ],

    // Onde as fotos ficam salvas. Não é segredo — pode ficar aqui (diferente
    // de config/autoload/local.php, reservado pra credenciais/segredos).
    // Reaproveitado por Api\Controller\PessoaController (mesmo Service).
    'pessoa' => [
        'uploads_dir' => __DIR__ . '/../../../public/uploads/pessoas',
    ],

    'doctrine' => [
        'driver' => [
            'pessoa_entities' => [
                'class' => AttributeDriver::class,
                'paths' => [__DIR__ . '/../src/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    'Pessoa\Entity' => 'pessoa_entities',
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'pessoa/pessoa/index' => __DIR__ . '/../view/pessoa/pessoa/index.phtml',
            'pessoa/pessoa/add' => __DIR__ . '/../view/pessoa/pessoa/form.phtml',
            'pessoa/pessoa/edit' => __DIR__ . '/../view/pessoa/pessoa/form.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
