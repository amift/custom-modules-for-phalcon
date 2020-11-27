<?php

return [
    'view_strategy' => [
        'polls' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Polls/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, 'module/Polls/src/Polls/Entity'),
            ],
        ],
    ],
    'settings' => [
        'page_size' => [
            'polls' => 20,
        ],
    ],
];