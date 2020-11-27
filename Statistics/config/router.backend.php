<?php

if (isConsoleRequest()) {
    return [];
}

return [
    'router' => [
        'routes' => [
            /**
             * Types
             */
            'sport_types_list' => [
                'route' => '/statistics/types',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'types',
                    'action'        => 'index',
                ],
            ],
            'sport_types_add' => [
                'route' => '/statistics/types/add',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'types',
                    'action'        => 'add',
                ],
            ],
            'sport_types_edit' => [
                'route' => '/statistics/types/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'types',
                    'action'        => 'edit'
                ],
            ],
            'sport_types_articles_categories_ajax_load' => [
                'route' => '/statistics/types/article-catgories-load-json/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'types',
                    'action'        => 'articlesCategoriesLoadJson'
                ],
            ],
            /**
             * Leagues
             */
            'sport_leagues_list' => [
                'route' => '/statistics/leagues',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'leagues',
                    'action'        => 'index',
                ],
            ],
            'sport_leagues_add' => [
                'route' => '/statistics/leagues/add',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'leagues',
                    'action'        => 'add',
                ],
            ],
            'sport_leagues_edit' => [
                'route' => '/statistics/leagues/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'leagues',
                    'action'        => 'edit'
                ],
            ],
            'sport_leagues_ajax_load' => [
                'route' => '/statistics/leagues/load-items-json/{type:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'leagues',
                    'action'        => 'loadItemsJson'
                ],
            ],
            'sport_leagues_groups_ajax_load' => [
                'route' => '/statistics/league-groups/load-items-json/{league:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'leagues',
                    'action'        => 'loadGroupsItemsJson'
                ],
            ],
            /**
             * Teams
             */
            'sport_teams_list' => [
                'route' => '/statistics/teams',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'teams',
                    'action'        => 'index',
                ],
            ],
            'sport_teams_add' => [
                'route' => '/statistics/teams/add',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'teams',
                    'action'        => 'add',
                ],
            ],
            'sport_teams_edit' => [
                'route' => '/statistics/teams/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'teams',
                    'action'        => 'edit'
                ],
            ],
            'sport_teams_ajax_load' => [
                'route' => '/statistics/teams/load-items-json/{type:[0-9]+}/{league:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'leagues',
                    'action'        => 'loadItemsJson'
                ],
            ],
            /**
             * Seasons
             */
            'sport_seasons_list' => [
                'route' => '/statistics/seasons',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'seasons',
                    'action'        => 'index',
                ],
            ],
            'sport_seasons_add' => [
                'route' => '/statistics/seasons/add',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'seasons',
                    'action'        => 'add',
                ],
            ],
            'sport_seasons_edit' => [
                'route' => '/statistics/seasons/edit/{id:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'seasons',
                    'action'        => 'edit'
                ],
            ],
            'sport_seasons_ajax_load_by_league' => [
                'route' => '/statistics/seasons/load-items-json/{league:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'seasons',
                    'action'        => 'loadItemsJsonByLeague'
                ],
            ],
            /**
             * Statistics Overall
             */
            'sport_stats_overall_list' => [
                'route' => '/statistics/overall',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'statsoverall',
                    'action'        => 'index',
                ],
            ],
            'sport_stats_overall_view' => [
                'route' => '/statistics/overall/view/{typeId:[0-9]+}-{leagueId:[0-9]+}-{leagueGroupId:[0-9]+}-{seasonId:[0-9]+}',
                'defaults' => [
                    'module'        => 'Statistics',
                    'namespace'     => 'Statistics\Controller\Backend',
                    'controller'    => 'statsoverall',
                    'action'        => 'view'
                ],
            ],
        ],
    ],
];
