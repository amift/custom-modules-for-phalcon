<?php

return [
    'view_strategy' => [
        'articles' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Articles/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, 'module/Articles/src/Articles/Entity'),
            ],
        ],
        'eventmanager' => [
            'subscribers' => [
                'category_listener',
            ],
        ],
    ],
    'settings' => [
        'masonry' => true,
        'page_size' => [
            'articles' => 20,
            'categories' => 50,
            'comments' => 20,
        ],
    ],
];