<?php

return [
    'router' => [
        'routes' => [
            'document_lvl1' => [
                'route' => '/doc/{lvl1:[a-z0-9-]+}',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Frontend',
                    'controller'    => 'documents',
                    'action'        => 'index',
                ],
            ],
            'document_lvl2' => [
                'route' => '/doc/{lvl1:[a-z0-9-]+}/{lvl2:[a-z0-9-]+}',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Frontend',
                    'controller'    => 'documents',
                    'action'        => 'index',
                ],
            ],
            
            // This is used for pretty links
            'document_advertising_opportunities' => [
                'route' => '/reklamas-iespejas',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Frontend',
                    'controller'    => 'documents',
                    'action'        => 'showByKey',
                    'key'           => 'advertising-opportunities',
                ],
            ],
            'document_terms' => [
                'route' => '/lietosanas-noteikumi',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Frontend',
                    'controller'    => 'documents',
                    'action'        => 'showByKey',
                    'key'           => 'terms',
                ],
            ],
            'document_faq' => [
                'route' => '/jautajumi-un-atbildes',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Frontend',
                    'controller'    => 'documents',
                    'action'        => 'showByKey',
                    'key'           => 'faq',
                ],
            ],
            'document_how_it_works' => [
                'route' => '/portala-darbibas-princips',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Frontend',
                    'controller'    => 'documents',
                    'action'        => 'showByKey',
                    'key'           => 'how-it-works',
                ],
            ],
        ],
    ],
];
