<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            'notifications_test' => [
                'route' => '/notifications-log/send-mail-test',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'notifications',
                    'action'        => 'sendMailTest'
                ],
            ],
            'notifications_list' => [
                'route' => '/notifications-log',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'notifications',
                    'action'        => 'index',
                ],
            ],
            'notifications_view' => [
                'route' => '/notifications-log/view/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'notifications',
                    'action'        => 'view'
                ],
            ],
            'notification_templates_list' => [
                'route' => '/notification-templates',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'templates',
                    'action'        => 'index',
                ],
            ],
            'notification_templates_edit' => [
                'route' => '/notification-templates/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'templates',
                    'action'        => 'edit'
                ],
            ],
            'notification_templates_add' => [
                'route' => '/notification-templates/add',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'templates',
                    'action'        => 'add'
                ],
            ],
            'newsletters_list' => [
                'route' => '/newsletters',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'newsletter',
                    'action'        => 'index',
                ],
            ],
            'newsletters_edit' => [
                'route' => '/newsletters/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'newsletter',
                    'action'        => 'edit'
                ],
            ],
            'newsletters_add' => [
                'route' => '/newsletters/add',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'newsletter',
                    'action'        => 'add'
                ],
            ],
            'newsletters_search_articles_ajax' => [
                'route' => '/newsletters/search-for-articles',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'newsletter',
                    'action'        => 'searchForArticlesAjax'
                ],
            ],
            'newsletters_preview_ajax' => [
                'route' => '/newsletters/preview/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Communication',
                    'namespace'     => 'Communication\Controller\Backend',
                    'controller'    => 'newsletter',
                    'action'        => 'previewAjax'
                ],
            ],
        ],
    ],
];
