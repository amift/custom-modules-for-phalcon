<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            'polls_list' => [
                'route' => '/polls',
                'defaults' => [
                    'module'        => 'Polls',
                    'namespace'     => 'Polls\Controller\Backend',
                    'controller'    => 'polls',
                    'action'        => 'index',
                ],
            ],
            'polls_view' => [
                'route' => '/polls/view/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Polls',
                    'namespace'     => 'Polls\Controller\Backend',
                    'controller'    => 'polls',
                    'action'        => 'view'
                ],
            ],
            'polls_edit' => [
                'route' => '/polls/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Polls',
                    'namespace'     => 'Polls\Controller\Backend',
                    'controller'    => 'polls',
                    'action'        => 'edit'
                ],
            ],
            'polls_add' => [
                'route' => '/polls/add',
                'defaults' => [
                    'module'        => 'Polls',
                    'namespace'     => 'Polls\Controller\Backend',
                    'controller'    => 'polls',
                    'action'        => 'add'
                ],
            ],
        ],
    ],
];