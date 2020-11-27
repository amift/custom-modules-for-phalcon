<?php

return [
    'view_strategy' => [
        'documents' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Documents/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, '/module/Documents/src/Documents/Entity'),
            ],
        ],
    ],
];