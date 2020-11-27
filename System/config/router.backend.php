<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            'server_info_general' => [
                'route' => '/server-info/general',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'serverinfo',
                    'action'        => 'index',
                ],
            ],
            'server_info_php' => [
                'route' => '/server-info/php',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'serverinfo',
                    'action'        => 'php',
                ],
            ],
            'settings_list' => [
                'route' => '/settings',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'settings',
                    'action'        => 'index',
                ],
            ],
            'settings_edit' => [
                'route' => '/cron-jobs/view/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'settings',
                    'action'        => 'edit'
                ],
            ],
            'cronjobs_list' => [
                'route' => '/cron-jobs',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'cronjobs',
                    'action'        => 'index',
                ],
            ],
            'cronjobs_edit' => [
                'route' => '/cron-jobs/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'cronjobs',
                    'action'        => 'edit'
                ],
            ],
            'cronjobs_log' => [
                'route' => '/cron-jobs/log/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'cronjobs',
                    'action'        => 'log'
                ],
            ],
            'cronjobs_log_view' => [
                'route' => '/cron-jobs/log/view/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'cronjobs',
                    'action'        => 'logView'
                ],
            ],
            'cronjobs_run' => [
                'route' => '/cron-jobs/run/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'cronjobs',
                    'action'        => 'run'
                ],
            ],
            'cache_list' => [
                'route' => '/cached-data',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'cache',
                    'action'        => 'index',
                ],
            ],
            'cache_delete' => [
                'route' => '/cached-data/delete/{key:[a-zA-Z0-9-_]+}',
                'defaults' => [
                    'module'        => 'System',
                    'namespace'     => 'System\Controller\Backend',
                    'controller'    => 'cache',
                    'action'        => 'delete'
                ],
            ],
        ],
    ],
];