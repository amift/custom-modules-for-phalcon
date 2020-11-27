<?php

return [
    'router' => [
        'routes' => [
            'polls_category_show' => [
                'route' => '/{category:[a-z0-9-]+}/aptauja/{slug:[a-z0-9-]+}:{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Polls',
                    'namespace'     => 'Polls\Controller\Frontend',
                    'controller'    => 'polls',
                    'action'        => 'show'
                ],
            ],
            'polls_subcategory_show' => [
                'route' => '/{category:[a-z0-9-]+}/{subcategory:[a-z0-9-]+}/aptauja/{slug:[a-z0-9-]+}:{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Polls',
                    'namespace'     => 'Polls\Controller\Frontend',
                    'controller'    => 'polls',
                    'action'        => 'show'
                ],
            ],
            'poll_save_vote' => [
                'route' => '/polls/vote/{id:[0-9]+}/{option_id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Polls',
                    'namespace'     => 'Polls\Controller\Frontend',
                    'controller'    => 'polls',
                    'action'        => 'saveVoteAjax'
                ],
            ],
            'poll_save_comment' => [
                'route' => '/poll/comment/add/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Polls',
                    'namespace'     => 'Polls\Controller\Frontend',
                    'controller'    => 'polls',
                    'action'        => 'savePollCommentAjax'
                ],
            ],
            'poll_load_comments' => [
                'route' => '/poll/comment/list/{id:[0-9]+}/{order:[a-z]+}/{page:[0-9]+}',
                'defaults' => [
                    'module'        => 'Polls',
                    'namespace'     => 'Polls\Controller\Frontend',
                    'controller'    => 'polls',
                    'action'        => 'loadPollCommentsAjax'
                ],
            ],
        ],
    ],
];
