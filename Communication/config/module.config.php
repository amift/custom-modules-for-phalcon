<?php

return [
    'view_strategy' => [
        'communication' => [
            'view_dir' => ROOT_PATH . str_replace('/', DS, '/module/Communication/view/' . APP_TYPE) . DS,
        ],
    ],
    'doctrine' => [
        'configuration' => [
            'entity_path' => [
                ROOT_PATH . str_replace('/', DS, 'module/Communication/src/Communication/Entity'),
            ],
        ],
        'eventmanager' => [
            'subscribers' => [
                'template_listener',
            ],
        ],
    ],
    'settings' => [
        'page_size' => [
            'notifications' => 20,
            'templates' => 20,
            'newsletters' => 20,
        ],
    ],
    'communication' => [
        'default' => [
            'placeholders' => [
                'username', 'url_login', 'url_registration'//, 'url_unsubscribe'
            ],
            'fromName' => 'AllSports.LV',
            'fromEmail' => 'no-reply@allsports.lv',
        ],
    ],
];