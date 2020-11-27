<?php

return [
    'router' => [
        'routes' => [
            'member_login' => [
                'route' => '/pieslegties',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'login',
                ],
            ],
            'member_logout' => [
                'route' => '/iziet',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'logout',
                ],
            ],
            'member_register' => [
                'route' => '/registreties',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'register',
                ],
            ],
            'member_registered_success' => [
                'route' => '/atlicis-vefiricet-epasta-adresi',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'registeredSuccess',
                ],
            ],
            'member_activation' => [
                'route' => '/profils/konta-aktivizesana/{code:[a-zA-Z0-9-]+}',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'accountActivation',
                ],
            ],
            'member_reset_password' => [
                'route' => '/atjaunot-paroli',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'resetPassword',
                ],
            ],
            'member_reset_password_success' => [
                'route' => '/pagaidu-parole-izveidota',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'resetPasswordSuccess',
                ],
            ],
            'member_profile' => [
                'route' => '/profils',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'profile',
                ],
            ],
            'member_change_password' => [
                'route' => '/profils/paroles-maina',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'changePassword',
                ],
            ],
            'member_earnings' => [
                'route' => '/profils/pelna',
                'defaults' => [
                    'module'        => 'Members',
                    'namespace'     => 'Members\Controller\Frontend',
                    'controller'    => 'members',
                    'action'        => 'earnings',
                ],
            ],
        ],
    ],
];
