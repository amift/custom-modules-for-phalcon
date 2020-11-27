<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            'bookings_list' => [
                'route' => '/bookings',
                'defaults' => [
                    'module'        => 'Bookings',
                    'namespace'     => 'Bookings\Controller\Backend',
                    'controller'    => 'bookings',
                    'action'        => 'index',
                ],
            ],
            'bookings_add' => [
                'route' => '/bookings/add',
                'defaults' => [
                    'module'        => 'Bookings',
                    'namespace'     => 'Bookings\Controller\Backend',
                    'controller'    => 'bookings',
                    'action'        => 'add'
                ],
            ],
        ],
    ],
];
