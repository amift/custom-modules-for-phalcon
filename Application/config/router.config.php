<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'not_found_route' => [
            'module'        => 'Application',
            'namespace'     => 'Application\Controller\\' . ucfirst(APP_TYPE),
            'controller'    => 'error',
            'action'        => 'notFound',
        ],
        'routes' => [
            'error_no_permissions' => [
                'route' => '/no-permission',
                'defaults' => [
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\\' . ucfirst(APP_TYPE),
                    'controller'    => 'error',
                    'action'        => 'noPermission',
                ],
            ],
            /*'error_system' => [
                'route' => '/page-not-avialable',
                'defaults' => [
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\\' . ucfirst(APP_TYPE),
                    'controller'    => 'error',
                    'action'        => 'systemError',
                ],
            ],*/
            '/' => [
                'route' => '/',
                'defaults' => [
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\\' . ucfirst(APP_TYPE),
                    'controller'    => 'application',
                    'action'        => 'index',
                ],
            ],
        ],
    ],
];