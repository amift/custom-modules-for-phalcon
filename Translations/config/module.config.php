<?php

return [
    'view_strategy' => [
        'translations' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Translations/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, '/module/Translations/src/Translations/Entity'),
            ],
        ],
    ],
    'settings' => [
        'page_size' => [
            'translations' => 30,
        ],
    ],
];