<?php

return [
    'view_strategy' => [
        'system' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/System/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, '/module/System/src/System/Entity'),
            ],
        ],
    ],
];