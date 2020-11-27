<?php

return [
    'view_strategy' => [
        'forum' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Forum/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, 'module/Forum/src/Forum/Entity'),
            ],
        ],
        'eventmanager' => [
            'subscribers' => [
                'forum_category_listener',
            ],
        ],
    ],
    'settings' => [
        'page_size' => [
            'categories' => 50,
            'replies' => 20,
            'topics' => 20,
        ],
    ],
];