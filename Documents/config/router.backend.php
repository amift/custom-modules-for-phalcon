<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            'documents_list' => [
                'route' => '/documents',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Backend',
                    'controller'    => 'documents',
                    'action'        => 'index',
                ],
            ],
            'documents_add' => [
                'route' => '/documents/add',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Backend',
                    'controller'    => 'documents',
                    'action'        => 'add',
                ],
            ],
            'documents_edit' => [
                'route' => '/documents/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Backend',
                    'controller'    => 'documents',
                    'action'        => 'edit'
                ],
            ],
            'documents_delete' => [
                'route' => '/documents/delete/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Documents',
                    'namespace'     => 'Documents\Controller\Backend',
                    'controller'    => 'documents',
                    'action'        => 'delete'
                ],
            ],
        ],
    ],
];