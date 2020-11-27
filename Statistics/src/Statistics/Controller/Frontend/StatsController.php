<?php

namespace Statistics\Controller\Frontend;

use Common\Controller\AbstractFrontendController;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Statistics\Entity\SportLeague;
use Statistics\Entity\SportLeagueGroup;
use Statistics\Entity\SportSeason;
use Statistics\Entity\SportStatsOverall;
use Statistics\Entity\SportType;
use Translations\Tool\Group;

class StatsController extends AbstractFrontendController
{

    /**
     * @var \Statistics\Repository\SportLeagueRepository
     */
    protected $_leagueRepo;

    /**
     * @var \Statistics\Repository\SportLeagueGroupRepository
     */
    protected $_leagueGroupRepo;

    /**
     * @var \Statistics\Repository\SportSeasonRepository
     */
    protected $_seasonRepo;

    /**
     * @var \Statistics\Repository\SportStatsOverallRepository
     */
    protected $_statsOverallRepo;

    /**
     * @var \Statistics\Repository\SportTypeRepository
     */
    protected $_typeRepo;

    public function indexAction()
    {
        $contentClass = 'page-content';
        $enableCoverAdd = false;

        $result['currentPage'] = (int)$this->request->getQuery('page', 'int', 1);
        $result['perPage'] = 20;
        $result['fullUrl'] = $this->config->web_url . $this->url->get(['for' => 'stats_list']);
        
        // Get stats rows
        $qb = $this->getStatsOverallRepo()->createQueryBuilder('so')
                ->select('so, type, season, league')
                ->leftJoin('so.sportType', 'type')
                ->leftJoin('so.sportLeague', 'league')
                ->leftJoin('so.sportSeason', 'season')
                ->groupBy('so.sportLeague')
                ->orderBy('so.id', 'DESC')
                ->setFirstResult(($result['currentPage'] - 1) * $result['perPage'])
                ->setMaxResults($result['perPage']);
        $result['paginator'] = new Paginator($qb->getQuery(), true);
        
        // Get meta data
        $meta = [
            'title' => $this->translator->trans('stats_list_page_title', 'Statistika', Group::STATISTICS),
            'description' => $this->translator->trans('stats_list_page_description', 'Populārako līgu turnīra tabulu statistika', Group::STATISTICS),
            'keywords' => '',
            'autoKeys' => ['statistika', 'turnīra tabula', 'populārākās līgas'],
        ];

        // Set up meta data
        if ($meta['title'] !== '') {
            $this->metaData->setTitle($meta['title']);
            $this->metaData->enableAddTitleSuffix();
        }
        if ($meta['description'] !== '') {
            $this->metaData->setDescription($meta['description']);
        }
        if ($meta['keywords'] !== '') {
            $this->metaData->setKeywords($meta['keywords']);
        } else {
            $this->metaData->createKeywordsFromText('', 4, true, (count($meta['autoKeys']) > 0 ? implode(', ', $meta['autoKeys']) : ''));
        }
        $this->metaData->setLinkCanonical($result['fullUrl']);
                    
        $this->view->setVars(compact('contentClass', 'enableCoverAdd', 'result'));
    }

    public function fullStatsByArticleSubcategoryAction()
    {
        $category       = $this->dispatcher->getParam('category', 'string', '');
        $subcategory    = $this->dispatcher->getParam('subcategory', 'string', '');
        $sportTypeIds   = [];
        $sportLeagueIds = [];

        $fullUrl = $this->config->web_url;
        if ($category !== '') {
            $enableCoverAdd = true;
            $fullUrl.= '/' . $category;
            if ($subcategory !== '') {
                $enableCoverAdd = true;
                $fullUrl.= '/' . $subcategory;
            }
        }

        // Check if category url params realy exists
        $loadStats = false;
        if ($category !== '' && $subcategory !== '') {
            if ($this->categoriesService->isCategoriesParamsReal($category, $subcategory)) {
                $params = $this->categoriesService->getCategoryFilterCriteria($category, $subcategory);
                if ($params['name'] !== null && $params['value'] !== null) {
                    if ((int)$params['value'] > 0) {
                        switch ($params['name']) {
                            case 'categoryLvl2' :
                                $sportLeagueIds = $this->statisticsService->getSportLeagueIdsByArticleCategory($params['value']);
                                if (count($sportLeagueIds) > 0) {
                                    $loadStats = true;
                                }
                                break;
                        }
                    }
                }
            }
        }

        if (!$loadStats) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'notFound',
            ]);
            return false;
        }

        $seasonKey = $this->statisticsService->getDefaultStatsParam($sportTypeIds, $sportLeagueIds);
        if ($seasonKey === '') {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'notFound',
            ]);
            return false;
        }
        $this->dispatcher->setParam('seasonkey', $seasonKey);
        $this->dispatcher->forward([
            'module'        => 'Statistics',
            'namespace'     => 'Statistics\Controller\Frontend',
            'controller'    => 'stats',
            'action'        => 'fullTable',
        ]);
        //
    }

    public function fullTableAction($category = '', $subcategory = '', $seasonkey = '')
    {
        $contentClass = 'page-content';
        $enableCoverAdd = false;
        $params = $this->dispatcher->getParam('seasonkey', 'string', '');
        
        $urlReal = $urlAlternate = '';
        if ($category !== '' && $subcategory !== '' && $seasonkey !== '') {
            if ($seasonkey === $params) {
                $urlReal = $this->config->web_url . $this->url->get(['for' => 'stats_full_table', 'seasonkey' => $params]);
                $urlAlternate = $this->config->web_url . $this->url->get(['for' => 'stats_full_by_article_subcategory', 'category' => $category, 'subcategory' => $subcategory]);
            }
        } else {
            if ($category === $params) {
                $urlReal = $urlAlternate = $this->config->web_url . $this->url->get(['for' => 'stats_full_table', 'seasonkey' => $params]);
            }
        }

        list($typeId, $leagueId, $seasonId) = explode('-', $params, 3);

        $sportType          = $this->getTypeRepo()->findObjectById($typeId);
        $sportLeague        = $this->getLeagueRepo()->findObjectById($leagueId);
        $season             = $this->getSeasonRepo()->findObjectById($seasonId);

        if (
            !is_object($sportType) || !is_object($sportLeague) || !is_object($season)
        ) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'notFound',
            ]);
            return false;
        }

        $sportLeagueGroupIds = $this->statisticsService->getStatsActualLeagueGroupsIds($sportType, $sportLeague, $season);
        if (count($sportLeagueGroupIds) < 1) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'notFound',
            ]);
            return false;
        }

        $autoKeys = [
            $sportType->getTitle(),
            $sportLeague->getTitle(),
            $season->getTitle()
        ];

        $stats = [];
        foreach ($sportLeagueGroupIds as $sportLeagueGroupId) {
            $sportLeagueGroup = $this->getLeagueGroupRepo()->findObjectById($sportLeagueGroupId);
            if (is_object($sportLeagueGroup)) {
                $autoKeys[] = $sportLeagueGroup->getTitle();
                $table = $this->getStatsOverallRepo()->getOverAllStatsTable(
                    $sportType->getId(),
                    $sportLeague->getId(),
                    $sportLeagueGroupId,
                    $season->getId()
                );
                if (count($table) > 0) {
                    $stats[] = [
                        'sportLeagueGroup' => $sportLeagueGroup,
                        'table' => $table
                    ];
                }
            }
        }

        $breadcrumbUrls = [];
        if ($category !== '' && $subcategory !== '') {
            $breadcrumbUrls[] = [
                'url' => $this->config->web_url . $this->url->get(['for' => 'articles_category', 'category' => $category]),
                'title' => $sportType->getTitle()
            ];
            $breadcrumbUrls[] = [
                'url' => $this->config->web_url . $this->url->get(['for' => 'articles_subcategory', 'category' => $category, 'subcategory' => $subcategory]),
                'title' => $sportLeague->getTitle()
            ];
        }

        $titleParts = [
            $sportType->getTitle(),
            $sportLeague->getTitle(),
            $season->getTitle()
        ];
        $metaTitle = sprintf('%s - %s', $this->translator->trans('stats_share_title_prefix', 'Statistika', Group::STATISTICS), implode(' / ', $titleParts));
        $metaDescription = sprintf('%s %s', $this->translator->trans('stats_share_description_prefix', 'Pilna statistikas tabula', Group::STATISTICS), implode(' / ', $titleParts));

        // Set up meta data
        $this->metaData->setTitle($metaTitle);
        $this->metaData->enableAddTitleSuffix();
        $this->metaData->setDescription($metaDescription);
        $this->metaData->createKeywordsFromText('', 4, true, (count($autoKeys) > 0 ? implode(', ', $autoKeys) : ''));
        $this->metaData->setLinkCanonical($urlReal);

        $this->view->setVars(compact('contentClass', 'enableCoverAdd', 'stats', 'sportType', 'sportLeague', 'season', 'urlReal', 'urlAlternate', 'breadcrumbUrls'));
    }

    public function getSidebarStatsTableAjaxAction()
    {
        if ($this->request->isAjax() !== true && $this->request->isPost() !== true) {
            $error = $this->translator->trans('error_invalid_access', 'Invalid access', Group::NOT_SET);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        try {
            $params = $this->request->getPost('params');
            $statsHtmlTable = $this->statisticsService->checkStatsMainParamsAndSidebarTableHtml($params);
        } catch (\Exception $exc) {
            $error = $this->translator->trans($exc->getMessage(), $exc->getMessage(), Group::STATISTICS);
            $this->response->setJsonContent(['success' => 0, 'errors' => $error]);
            return $this->response->send();
        }

        $this->response->setJsonContent(['success' => 1, 'html' => $statsHtmlTable]);

        return $this->response->send();
    }

    /**
     * Get SportLeague entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportLeagueRepository
     */
    protected function getLeagueRepo()
    {
        if ($this->_leagueRepo === null || !$this->_leagueRepo) {
            $this->_leagueRepo = $this->getEntityRepository(SportLeague::class);
        }

        return $this->_leagueRepo;
    }

    /**
     * Get SportLeagueGroup entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportLeagueGroupRepository
     */
    protected function getLeagueGroupRepo()
    {
        if ($this->_leagueGroupRepo === null || !$this->_leagueGroupRepo) {
            $this->_leagueGroupRepo = $this->getEntityRepository(SportLeagueGroup::class);
        }

        return $this->_leagueGroupRepo;
    }

    /**
     * Get SportSeason entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportSeasonRepository
     */
    protected function getSeasonRepo()
    {
        if ($this->_seasonRepo === null || !$this->_seasonRepo) {
            $this->_seasonRepo = $this->getEntityRepository(SportSeason::class);
        }

        return $this->_seasonRepo;
    }

    /**
     * Get SportStatsOverall entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportStatsOverallRepository
     */
    protected function getStatsOverallRepo()
    {
        if ($this->_statsOverallRepo === null || !$this->_statsOverallRepo) {
            $this->_statsOverallRepo = $this->getEntityRepository(SportStatsOverall::class);
        }

        return $this->_statsOverallRepo;
    }

    /**
     * Get SportType entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportTypeRepository
     */
    protected function getTypeRepo()
    {
        if ($this->_typeRepo === null || !$this->_typeRepo) {
            $this->_typeRepo = $this->getEntityRepository(SportType::class);
        }

        return $this->_typeRepo;
    }

}
