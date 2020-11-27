<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            'user_login' => [
                'route' => '/login',
                'defaults' => [
                    'module'        => 'Users',
                    'namespace'     => 'Users\Controller\Backend',
                    'controller'    => 'user',
                    'action'        => 'login',
                ],
            ],
            'user_logout' => [
                'route' => '/logout',
                'defaults' => [
                    'module'        => 'Users',
                    'namespace'     => 'Users\Controller\Backend',
                    'controller'    => 'user',
                    'action'        => 'logout',
                ],
            ],
            'user_profile' => [
                'route' => '/profile',
                'defaults' => [
                    'module'        => 'Users',
                    'namespace'     => 'Users\Controller\Backend',
                    'controller'    => 'user',
                    'action'        => 'profile',
                ],
            ],
            /**
             * !!! BAD PRACTICE, IF CAN BETTER USE DIRECT ROUTES
             * 
             * 'users' => [
                'route' => '/users/:action/:params',
                'defaults' => [
                    'module'        => 'Users',
                    'namespace'     => 'Users\Controller\Backend',
                    'controller'    => 'users',
                    'action'        => 1,
                    'params'        => 2
                ],
            ],*/
            'users_list' => [
                'route' => '/users',
                'defaults' => [
                    'module'        => 'Users',
                    'namespace'     => 'Users\Controller\Backend',
                    'controller'    => 'users',
                    'action'        => 'index',
                ],
            ],
            'users_add' => [
                'route' => '/users/add',
                'defaults' => [
                    'module'        => 'Users',
                    'namespace'     => 'Users\Controller\Backend',
                    'controller'    => 'users',
                    'action'        => 'add',
                ],
            ],
            'users_view' => [
                'route' => '/users/view/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Users',
                    'namespace'     => 'Users\Controller\Backend',
                    'controller'    => 'users',
                    'action'        => 'view'
                ],
            ],
            'users_edit' => [
                'route' => '/users/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Users',
                    'namespace'     => 'Users\Controller\Backend',
                    'controller'    => 'users',
                    'action'        => 'edit'
                ],
            ],
            'users_password' => [
                'route' => '/users/password/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Users',
                    'namespace'     => 'Users\Controller\Backend',
                    'controller'    => 'users',
                    'action'        => 'password'
                ],
            ],
        ],
    ],
];