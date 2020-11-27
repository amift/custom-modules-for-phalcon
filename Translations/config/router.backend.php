<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            'translations_list' => [
                'route' => '/translations',
                'defaults' => [
                    'module'        => 'Translations',
                    'namespace'     => 'Translations\Controller\Backend',
                    'controller'    => 'translations',
                    'action'        => 'index',
                ],
            ],
            'translations_edit' => [
                'route' => '/translations/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Translations',
                    'namespace'     => 'Translations\Controller\Backend',
                    'controller'    => 'translations',
                    'action'        => 'edit'
                ],
            ],
            /*'translations_add' => [
                'route' => '/translations/add',
                'defaults' => [
                    'module'        => 'Translations',
                    'namespace'     => 'Translations\Controller\Backend',
                    'controller'    => 'translations',
                    'action'        => 'add',
                ],
            ],*/
        ],
    ],
];