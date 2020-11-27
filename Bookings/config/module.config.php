<?php

return [
    'view_strategy' => [
        'bookings' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Bookings/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, 'module/Bookings/src/Bookings/Entity'),
            ],
        ],
    ],
    'settings' => [
        'page_size' => [
            'bookings' => 20,
        ],
    ],
];