<?php

namespace Statistics\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Statistics\Entity\SportLeague;
use Statistics\Entity\SportLeagueGroup;
use Statistics\Entity\SportSeason;
use Statistics\Entity\SportStatsOverall;
use Statistics\Entity\SportType;

class StatsoverallController extends AbstractBackendController
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

    /**
     * Stats overall list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->stats;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $options = [
            'sportTypes' => $this->getSportTypesOptionsList(),
            'sportLeagues' => $this->getSportLeaguesOptionsList($this->request->getQuery('sportType', null, null)),
            'sportSeasons' => $this->getSportSeasonsOptionsList($this->request->getQuery('sportLeague', null, null)),
        ];

        $qb = $this->getStatsOverallRepo()->createQueryBuilder('so')
                ->select('so, type, season, league, leagueGroup')
                ->leftJoin('so.sportType', 'type')
                ->leftJoin('so.sportLeague', 'league')
                ->leftJoin('so.sportLeagueGroup', 'leagueGroup')
                ->leftJoin('so.sportSeason', 'season')
                ->groupBy('so.sportLeagueGroup')
                ->addGroupBy('so.sportSeason')
                ->orderBy('so.id', 'DESC')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage);

        $filters = new Filters($this->request);
        $filters
            ->addField('sportType', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('so.sportType = :sportType');
                    $qb->setParameter('sportType', $value);
                }
            })
            ->addField('sportLeague', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('so.sportLeague = :sportLeague');
                    $qb->setParameter('sportLeague', $value);
                }
            })
            ->addField('sportSeason', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('so.sportSeason = :sportSeason');
                    $qb->setParameter('sportSeason', $value);
                }
            })
        ;

        $filters->apply($qb, 'so');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('perPage', 'currentPage', 'options', 'paginator', 'filters'));
    }

    /**
     * Stats overall table view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function viewAction()
    {
        $typeId             = $this->dispatcher->getParam('typeId', null, null);
        $leagueId           = $this->dispatcher->getParam('leagueId', null, null);
        $leagueGroupId      = $this->dispatcher->getParam('leagueGroupId', null, null);
        $seasonId           = $this->dispatcher->getParam('seasonId', null, null);

        // Check if url prams is valid
        $sportType          = $this->getTypeRepo()->findObjectById($typeId);
        $sportLeague        = $this->getLeagueRepo()->findObjectById($leagueId);
        $sportLeagueGroup   = $this->getLeagueGroupRepo()->findObjectById($leagueGroupId);
        $season             = $this->getSeasonRepo()->findObjectById($seasonId);

        if (
            !is_object($sportType) || !is_object($sportLeague) || 
            !is_object($sportLeagueGroup) || !is_object($season)
        ) {
            $this->flashSession->error(sprintf('Statisctics by url params not exists'));
            return $this->response->redirect($this->url->get(['for' => 'sport_stats_overall_list']));
        }

        // Get stats table
        $table = $this->getStatsOverallRepo()->getOverAllStatsTable(
            $sportType->getId(), //$typeId, 
            $sportLeague->getId(), //$leagueId, 
            $sportLeagueGroup->getId(), //$leagueGroupId, 
            $season->getId() //$seasonId 
        );

        if (count($table) < 1) {
            $this->flashSession->error(sprintf('Statisctics by url params not exists'));
            return $this->response->redirect($this->url->get(['for' => 'sport_stats_overall_list']));
        }

        $this->view->setVars(compact('table', 'sportType', 'sportLeague', 'sportLeagueGroup', 'season'));
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

    /**
     * Get sport types options list
     * 
     * @access protected
     * @return array
     */
    protected function getSportTypesOptionsList()
    {
        $rows = [];

        $list = $this->getTypeRepo()->getList();
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        return $rows;
    }

    /**
     * Get sport leagues options list
     * 
     * @access protected
     * @param null|int $sportTypeId
     * @return array
     */
    protected function getSportLeaguesOptionsList($sportTypeId = null)
    {
        $rows = [];

        if ($sportTypeId !== null) {
            $list = $this->getLeagueRepo()->getList($sportTypeId);
            foreach ($list as $row) {
                $rows[$row['id']] = sprintf('%s', $row['title']);
            }
        }

        return $rows;
    }

    /**
     * Get sport seasons options list
     * 
     * @access protected
     * @param null|int $sportLeagueId
     * @return array
     */
    protected function getSportSeasonsOptionsList($sportLeagueId = null)
    {
        $rows = [];

        if ($sportLeagueId !== null) {
            $list = $this->getSeasonRepo()->getList(null, $sportLeagueId);
            foreach ($list as $row) {
                $rows[$row['id']] = sprintf('%s', $row['title']);
            }
        }

        return $rows;
    }

}
