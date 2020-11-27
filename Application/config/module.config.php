<?php

return [
    'view_strategy' => [
        'application' => [
            'view_dir'       => ROOT_PATH . str_replace('/', DS, '/module/Application/view/' . APP_TYPE . '/templates/'),
            'layouts_dir'    => str_replace('/', DS, '../../' . APP_TYPE . '/layouts/'),
            'default_layout' => 'layout',
        ],
    ],
];