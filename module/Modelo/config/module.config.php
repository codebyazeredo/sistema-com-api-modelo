<?php

declare(strict_types=1);

namespace Modelo;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'modelo' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/modelo[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ModeloController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\ModeloController::class => Controller\Factory\ModeloControllerFactory::class,
        ],
    ],

    'doctrine' => [
        'driver' => [
            'modelo_entities' => [
                'class' => AttributeDriver::class,
                'paths' => [__DIR__ . '/../src/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    'Modelo\Entity' => 'modelo_entities',
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'modelo/modelo/index' => __DIR__ . '/../view/modelo/modelo/index.phtml',
            'modelo/modelo/add' => __DIR__ . '/../view/modelo/modelo/form.phtml',
            'modelo/modelo/edit' => __DIR__ . '/../view/modelo/modelo/form.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
