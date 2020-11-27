<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            'articles_list' => [
                'route' => '/articles',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'articles',
                    'action'        => 'index',
                ],
            ],
            'articles_edit' => [
                'route' => '/articles/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'articles',
                    'action'        => 'edit'
                ],
            ],
            'articles_add' => [
                'route' => '/articles/add',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'articles',
                    'action'        => 'add'
                ],
            ],
            'article_categories_list' => [
                'route' => '/article-categories',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'index',
                ],
            ],
            'article_categories_add' => [
                'route' => '/article-categories/add',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'add',
                ],
            ],
            'article_categories_edit' => [
                'route' => '/article-categories/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'edit'
                ],
            ],
            'article_categories_ajax_change_ordering' => [
                'route' => '/article-categories/ordering/{direction:[a-z]+}/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'orderingAjax'
                ],
            ],
            'article_categories_ajax_load' => [
                'route' => '/article-categories/load-childs-json/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'categories',
                    'action'        => 'loadChildsJson'
                ],
            ],
            'all_comments_list' => [
                'route' => '/comments',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'comments',
                    'action'        => 'index',
                ],
            ],
            'comment_edit' => [
                'route' => '/comments/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'comments',
                    'action'        => 'edit'
                ],
            ],
            'reported_comments_list' => [
                'route' => '/comments-reported',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'comments',
                    'action'        => 'reported',
                ],
            ],
            'reported_comment_accept' => [
                'route' => '/comments-reported/accept/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'comments',
                    'action'        => 'reportedAccept'
                ],
            ],
            'reported_comment_ignore' => [
                'route' => '/comments-reported/ignore/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Backend',
                    'controller'    => 'comments',
                    'action'        => 'reportedIgnore'
                ],
            ],
        ],
    ],
];