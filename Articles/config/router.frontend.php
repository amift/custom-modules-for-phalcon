<?php

return [
    'router' => [
        'routes' => [
            'articles_category' => [
                'route' => '/{category:[a-z0-9-]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'index',
                ],
            ],
            'articles_subcategory' => [
                'route' => '/{category:[a-z0-9-]+}/{subcategory:[a-z0-9-]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'index',
                ],
            ],
            'articles_category_show' => [
                'route' => '/{category:[a-z0-9-]+}/{slug:[a-z0-9-]+}:{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'show'
                ],
            ],
            'articles_subcategory_show' => [
                'route' => '/{category:[a-z0-9-]+}/{subcategory:[a-z0-9-]+}/{slug:[a-z0-9-]+}:{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'show'
                ],
            ],
            'article_add_textual' => [
                'route' => '/pievienot-zinu',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'addTextArticle',
                ],
            ],
            'article_add_video' => [
                'route' => '/pievienot-video-zinu',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'addVideoArticle',
                ],
            ],
            'article_categories_ajax_load' => [
                'route' => '/categories/json/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'loadChildsJson'
                ],
            ],
            'article_text_added' => [
                'route' => '/raksts-pievienots',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'articleTextAdded',
                ],
            ],
            'article_video_added' => [
                'route' => '/video-zina-pievienota',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'articleVideoAdded',
                ],
            ],
            'article_save_rate' => [
                'route' => '/article/rate/{type:[a-z]+}/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'rateArticleAjax'
                ],
            ],
            'article_save_comment' => [
                'route' => '/article/comment/add/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'saveArticleCommentAjax'
                ],
            ],
            'article_load_comments' => [
                'route' => '/article/comment/list/{id:[0-9]+}/{order:[a-z]+}/{page:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'loadArticleCommentsAjax'
                ],
            ],
            'comment_save_rate' => [
                'route' => '/comment/rate/{type:[a-z]+}/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'rateCommentAjax'
                ],
            ],
            'comment_save_reporting' => [
                'route' => '/comment/reporting/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'reportCommentAjax'
                ],
            ],
            'article_get_preview' => [
                'route' => '/article/preview',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'getArticlePreviewDataAjax'
                ],
            ],
            'articles_load_more' => [
                'route' => '/articles/load-more',
                'defaults' => [
                    'module'        => 'Articles',
                    'namespace'     => 'Articles\Controller\Frontend',
                    'controller'    => 'articles',
                    'action'        => 'loadMoreAjax'
                ],
            ],
        ],
    ],
];
