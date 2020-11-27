<?php

return [
    'view_strategy' => [
        'users' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Users/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, '/module/Users/src/Users/Entity'),
            ],
        ],
        'eventmanager' => [
            'subscribers' => [
                'user_listener',
            ],
        ],
    ],
];