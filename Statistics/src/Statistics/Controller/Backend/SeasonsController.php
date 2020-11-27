<?php

namespace Statistics\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Statistics\Entity\SportLeague;
use Statistics\Entity\SportSeason;
use Statistics\Entity\SportTeam;
use Statistics\Entity\SportType;
use Statistics\Forms\SportSeasonForm;

class SeasonsController extends AbstractBackendController
{

    /**
     * @var \Statistics\Repository\SportLeagueRepository
     */
    protected $_leagueRepo;

    /**
     * @var \Statistics\Repository\SportSeasonRepository
     */
    protected $_seasonRepo;

    /**
     * @var \Statistics\Repository\SportTeamRepository
     */
    protected $_teamRepo;

    /**
     * @var \Statistics\Repository\SportTypeRepository
     */
    protected $_typeRepo;

    /**
     * Sport seasons list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->seasons;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $options = [
            'sportTypes' => $this->getSportTypesOptionsList(),
            'sportLeagues' => $this->getSportLeaguesOptionsList($this->request->getQuery('sportType', null, null)),
        ];

        $qb = $this->getSeasonRepo()->createQueryBuilder('s')
                ->select('s, type, league')
                ->leftJoin('s.sportType', 'type')
                ->leftJoin('s.sportLeague', 'league')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('s.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('sportType', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('s.sportType = :sportType');
                    $qb->setParameter('sportType', $value);
                }
            })
            ->addField('sportLeague', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('s.sportLeague = :sportLeague');
                    $qb->setParameter('sportLeague', $value);
                }
            })
            ->addField('actual')
            ->searchInFields('search', [ 
                's.title', 's.key',
            ])
        ;

        $filters->apply($qb, 's');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('perPage', 'currentPage', 'options', 'paginator', 'filters'));
    }

    /**
     * Sport season add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $season = new SportSeason();
        $form = new SportSeasonForm($season, ['edit' => true]);
        $action = $this->url->get(['for' => 'sport_seasons_add']);
        $error  = '';

        if ($this->request->isPost()) {
            $form->bind($this->request->getPost(), $season);
            if ($form->isValid($this->request->getPost())) {
                try {
                    // Type
                    if ((int)$season->getSportType() > 0) {
                        $type = $this->getTypeRepo()->findObjectById($season->getSportType());
                        $season->setSportType($type);
                    } else {
                        $season->setSportType(null);
                    }
                    // League
                    if ((int)$season->getSportLeague() > 0) {
                        $league = $this->getLeagueRepo()->findObjectById($season->getSportLeague());
                        $season->setSportLeague($league);
                    } else {
                        $season->setSportLeague(null);
                    }
                    // Save data
                    $this->getEntityManager()->persist($season);
                    $this->getEntityManager()->flush();
                    $this->getEntityManager()->refresh($season);
                    if ($season->isActual()) {
                        $this->getSeasonRepo()->setActual($season);
                    }
                    // Back to list
                    $this->flashSession->success(sprintf('Sport season "%s" created successfully!', (string)$season));
                    return $this->response->redirect($this->url->get(['for' => 'sport_seasons_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('season', 'form', 'action', 'error'));
    }

    /**
     * Sport season edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $season = $this->getSeasonRepo()->findObjectById($id);

        if (null === $season) {
            $this->flashSession->error(sprintf('Sport season with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'sport_seasons_list']));
        }

        $form = new SportSeasonForm($season, ['edit' => true]);
        $action = $this->url->get(['for' => 'sport_seasons_edit', 'id' => $season->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            $form->bind($this->request->getPost(), $season);
            if ($form->isValid($this->request->getPost())) {
                try {
                    // Type
                    if ((int)$season->getSportType() > 0) {
                        $type = $this->getTypeRepo()->findObjectById($season->getSportType());
                        $season->setSportType($type);
                    } else {
                        $season->setSportType(null);
                    }
                    // League
                    if ((int)$season->getSportLeague() > 0) {
                        $league = $this->getLeagueRepo()->findObjectById($season->getSportLeague());
                        $season->setSportLeague($league);
                    } else {
                        $season->setSportLeague(null);
                    }
                    // Save data
                    $this->getEntityManager()->persist($season);
                    $this->getEntityManager()->flush();
                    $this->getEntityManager()->refresh($season);
                    if ($season->isActual()) {
                        $this->getSeasonRepo()->setActual($season);
                    }
                    // Back to list
                    $this->flashSession->success(sprintf('Sport season "%s" info updated successfully!', (string)$season));
                    return $this->response->redirect($this->url->get(['for' => 'sport_seasons_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('season', 'form', 'action', 'error'));
    }

    /**
     * Get seasons list by sport league.
     * 
     * @access public
     * @return json
     */
    public function loadItemsJsonByLeagueAction()
    {
        if ($this->request->isAjax() !== true) {
            $this->response->setJsonContent(['success' => 0, 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        $sportLeagueId = $this->dispatcher->getParam('league', null, null);
        $list = $this->getSeasonRepo()->getList(null, $sportLeagueId);

        $rows = [];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        $this->response->setJsonContent(['success' => 1, 'data' => $rows]);

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
     * Get SportTeam entity repository
     * 
     * @access protected
     * @return \Statistics\Repository\SportTeamRepository
     */
    protected function getTeamRepo()
    {
        if ($this->_teamRepo === null || !$this->_teamRepo) {
            $this->_teamRepo = $this->getEntityRepository(SportTeam::class);
        }

        return $this->_teamRepo;
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
     * Get sport teams options list
     * 
     * @access protected
     * @param null|int $sportTypeId
     * @param null|int $sportLeagueId
     * @return array
     */
    protected function getSportTeamsOptionsList($sportTypeId = null, $sportLeagueId = null)
    {
        $rows = [];

        if ($sportTypeId !== null && $sportLeagueId !== null) {
            $list = $this->getTeamRepo()->getList($sportTypeId, $sportLeagueId);
            foreach ($list as $row) {
                $rows[$row['id']] = sprintf('%s', $row['title']);
            }
        }

        return $rows;
    }

}
