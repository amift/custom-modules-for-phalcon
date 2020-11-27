<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            'members_list' => [
                'route' => '/members',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'members',
                    'action'        => 'index',
                ],
            ],
            'members_view' => [
                'route' => '/members/view/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'members',
                    'action'        => 'view'
                ],
            ],
            'members_edit' => [
                'route' => '/members/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'members',
                    'action'        => 'edit'
                ],
            ],
            'members_articles' => [
                'route' => '/members/articles/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'members',
                    'action'        => 'articles'
                ],
            ],
            'members_withdraws' => [
                'route' => '/members/withdraws/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'members',
                    'action'        => 'withdraws'
                ],
            ],
            'members_communication' => [
                'route' => '/members/communication/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'members',
                    'action'        => 'communication'
                ],
            ],
            'members_authorisations' => [
                'route' => '/members/authorisations/{id:[0-9]+}/{group:[a-zA-Z0-9]+}',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'members',
                    'action'        => 'authorisations',
                    'group'         => 'success'
                ],
            ],
            'withdraws_list' => [
                'route' => '/withdraws',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'withdraws',
                    'action'        => 'index',
                ],
            ],
            'withdraws_view' => [
                'route' => '/withdraws/view/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'withdraws',
                    'action'        => 'view'
                ],
            ],
            'withdraws_edit' => [
                'route' => '/withdraws/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Backend',
                    'controller'    => 'withdraws',
                    'action'        => 'edit'
                ],
            ],
        ],
    ],
];