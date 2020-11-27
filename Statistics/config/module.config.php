<?php

return [
    'view_strategy' => [
        'statistics' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Statistics/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, 'module/Statistics/src/Statistics/Entity'),
            ],
        ],
    ],
    'settings' => [
        'page_size' => [
            'types' => 20,
            'leagues' => 20,
            'teams' => 20,
            'seasons' => 20,
            'stats' => 20,
        ],
    ],
];