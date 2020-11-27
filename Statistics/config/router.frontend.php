<?php

return [
    'router' => [
        'routes' => [
            'stats_get_siderbar_table' => [
                'route' => '/statistics/get-sidebar-table',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Frontend',
                    'controller'    => 'stats',
                    'action'        => 'getSidebarStatsTableAjax'
                ],
            ],
            'stats_full_by_article_subcategory' => [
                'route' => '/{category:[a-z0-9-]+}/{subcategory:[a-z0-9-]+}/statistika',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Frontend',
                    'controller'    => 'stats',
                    'action'        => 'fullStatsByArticleSubcategory',
                ],
            ],
            'stats_full_table' => [
                'route' => '/statistika/{seasonkey:[a-z0-9-]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Frontend',
                    'controller'    => 'stats',
                    'action'        => 'fullTable',
                ],
            ],
            'stats_list' => [
                'route' => '/statistika',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Frontend',
                    'controller'    => 'stats',
                    'action'        => 'index',
                ],
            ],
            'stats_parsed_api_results' => [
                'route' => '/api/parsed/results/{key:[a-z0-9-]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Frontend',
                    'controller'    => 'results',
                    'action'        => 'result',
                ],
            ],
        ],
    ],
];
