<?php

return [
    'router' => [
        'routes' => [
            'forum_list' => [
                'route' => '/forums/:params',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'index',
                    'params'        => 1, // :params
                ],
            ],
            'forum_topic_by_category1' => [
                'route' => '/forums/{category1:[a-z0-9-]+}/{slug:[a-z0-9-]+}:{id:[0-9]+}/:params',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'show',
                    'params'        => 4,
                ],
            ],
            'forum_topic_by_category2' => [
                'route' => '/forums/{category1:[a-z0-9-]+}/{category2:[a-z0-9-]+}/{slug:[a-z0-9-]+}:{id:[0-9]+}/:params',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'show',
                    'params'        => 5,
                ],
            ],
            'forum_topic_by_category3' => [
                'route' => '/forums/{category1:[a-z0-9-]+}/{category2:[a-z0-9-]+}/{category3:[a-z0-9-]+}/{slug:[a-z0-9-]+}:{id:[0-9]+}/:params',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'show',
                    'params'        => 6,
                ],
            ],
            'forum_add_topic' => [
                'route' => '/forums/pievienot-temu',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'addTopic',
                ],
            ],
            'forum_topic_edit' => [
                'route' => '/forums/temas-redigesana/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'editTopic',
                ],
            ],
            'forum_categories_ajax_load' => [
                'route' => '/forum/categories/json/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'loadChildsJson'
                ],
            ],
            'forum_topic_get_preview' => [
                'route' => '/forum/topic/preview',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'getTopicPreviewDataAjax'
                ],
            ],
            'forum_topic_save_rate' => [
                'route' => '/forum/topic/rate/{type:[a-z]+}/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'rateTopicAjax'
                ],
            ],
            'forum_topic_reply_save' => [
                'route' => '/forum/topic/reply/add/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'saveTopicReplyAjax'
                ],
            ],
            'forum_topic_reply_rate' => [
                'route' => '/forum/topic/reply/rate/{type:[a-z]+}/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'rateTopicReplyAjax'
                ],
            ],
            'forum_topic_reply_report' => [
                'route' => '/forum/topic/reply/reporting/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Frontend',
                    'controller'    => 'forum',
                    'action'        => 'reportTopicReplyAjax'
                ],
            ],
        ],
    ],
];
