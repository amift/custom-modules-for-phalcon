<?php

return [
    'view_strategy' => [
        'members' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Members/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, 'module/Members/src/Members/Entity'),
            ],
        ],
        'eventmanager' => [
            'subscribers' => [
                'member_listener',
            ],
        ],
    ],
    'settings' => [
        'page_size' => [
            'members' => 20,
            'withdraws' => 20,
            'tab_list' => 20,
        ],
    ],
];