<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            
            'forum_categories_list' => [
                'route' => '/forum/categories',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'index',
                ],
            ],
            'forum_categories_add' => [
                'route' => '/forum/categories/add',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'add',
                ],
            ],
            'forum_categories_edit' => [
                'route' => '/forum/categories/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'edit'
                ],
            ],
            'forum_categories_ajax_change_ordering' => [
                'route' => '/forum/categories/ordering/{direction:[a-z]+}/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'orderingAjax'
                ],
            ],
            'forum_categories_ajax_load' => [
                'route' => '/forum/categories/load-childs-json/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'loadChildsJson'
                ],
            ],
            
            'forum_topics_list' => [
                'route' => '/forum/topics',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'topics',
                    'action'        => 'index',
                ],
            ],
            'forum_topics_edit' => [
                'route' => '/forum/topics/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'topics',
                    'action'        => 'edit'
                ],
            ],

            'forum_replies_list' => [
                'route' => '/forum/replies',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'replies',
                    'action'        => 'index',
                ],
            ],
            'forum_replies_edit' => [
                'route' => '/forum/replies/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'replies',
                    'action'        => 'edit'
                ],
            ],
            
            'forum_reported_replies_list' => [
                'route' => '/forum/replies-reported',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'replies',
                    'action'        => 'reported',
                ],
            ],
            'forum_reported_reply_accept' => [
                'route' => '/forum/replies-reported/accept/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'replies',
                    'action'        => 'reportedAccept'
                ],
            ],
            'forum_reported_reply_ignore' => [
                'route' => '/forum/replies-reported/ignore/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Forum',
                    'namespace'     => 'Forum\Controller\Backend',
                    'controller'    => 'replies',
                    'action'        => 'reportedIgnore'
                ],
            ],
        ],
    ],
];