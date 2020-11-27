<?php

namespace Statistics\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Statistics\Entity\SportLeague;
use Statistics\Entity\SportTeam;
use Statistics\Entity\SportType;
use Statistics\Forms\SportTeamForm;

class TeamsController extends AbstractBackendController
{

    /**
     * @var \Statistics\Repository\SportLeagueRepository
     */
    protected $_leagueRepo;

    /**
     * @var \Statistics\Repository\SportTeamRepository
     */
    protected $_teamRepo;

    /**
     * @var \Statistics\Repository\SportTypeRepository
     */
    protected $_typeRepo;

    /**
     * Sport teams list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->teams;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $options = [
            'sportTypes' => $this->getSportTypesOptionsList(),
            'sportLeagues' => $this->getSportLeaguesOptionsList($this->request->getQuery('sportType', null, null)),
        ];

        $qb = $this->getTeamRepo()->createQueryBuilder('t')
                ->select('t, type, league')
                ->leftJoin('t.sportType', 'type')
                ->leftJoin('t.sportLeague', 'league')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('t.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('sportType', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('t.sportType = :sportType');
                    $qb->setParameter('sportType', $value);
                }
            })
            ->addField('sportLeague', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('t.sportLeague = :sportLeague');
                    $qb->setParameter('sportLeague', $value);
                }
            })
            ->searchInFields('search', [ 
                't.title', 't.key',
            ])
        ;

        $filters->apply($qb, 't');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('perPage', 'currentPage', 'options', 'paginator', 'filters'));
    }

    /**
     * Sport team add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $team = new SportTeam();
        $form = new SportTeamForm($team, ['edit' => true]);
        $action = $this->url->get(['for' => 'sport_teams_add']);
        $error  = '';

        if ($this->request->isPost()) {
            $form->bind($this->request->getPost(), $team);
            if ($form->isValid($this->request->getPost())) {
                try {
                    // Type
                    if ((int)$team->getSportType() > 0) {
                        $type = $this->getTypeRepo()->findObjectById($team->getSportType());
                        $team->setSportType($type);
                    } else {
                        $team->setSportType(null);
                    }
                    // League
                    if ((int)$team->getSportLeague() > 0) {
                        $league = $this->getLeagueRepo()->findObjectById($team->getSportLeague());
                        $team->setSportLeague($league);
                    } else {
                        $team->setSportLeague(null);
                    }
                    // Save data
                    $this->getEntityManager()->persist($team);
                    $this->getEntityManager()->flush();
                    // Back to list
                    $this->flashSession->success(sprintf('Sport team "%s" created successfully!', (string)$team));
                    return $this->response->redirect($this->url->get(['for' => 'sport_teams_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('team', 'form', 'action', 'error'));
    }

    /**
     * Sport team edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $team = $this->getTeamRepo()->findObjectById($id);

        if (null === $team) {
            $this->flashSession->error(sprintf('Sport team with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'sport_teams_list']));
        }

        $form = new SportTeamForm($team, ['edit' => true]);
        $action = $this->url->get(['for' => 'sport_teams_edit', 'id' => $team->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            $form->bind($this->request->getPost(), $team);
            if ($form->isValid($this->request->getPost())) {
                try {
                    // Type
                    if ((int)$team->getSportType() > 0) {
                        $type = $this->getTypeRepo()->findObjectById($team->getSportType());
                        $team->setSportType($type);
                    } else {
                        $team->setSportType(null);
                    }
                    // League
                    if ((int)$team->getSportLeague() > 0) {
                        $league = $this->getLeagueRepo()->findObjectById($team->getSportLeague());
                        $team->setSportLeague($league);
                    } else {
                        $team->setSportLeague(null);
                    }
                    // Save data
                    $this->getEntityManager()->persist($team);
                    $this->getEntityManager()->flush();
                    // Back to list
                    $this->flashSession->success(sprintf('Sport team "%s" info updated successfully!', (string)$team));
                    return $this->response->redirect($this->url->get(['for' => 'sport_teams_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('team', 'form', 'action', 'error'));
    }

    /**
     * Get teams list by sport type and/or sport league.
     * 
     * @access public
     * @return json
     */
    public function loadItemsJsonAction()
    {
        if ($this->request->isAjax() !== true) {
            $this->response->setJsonContent(['success' => 0, 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        $sportTypeId = $this->dispatcher->getParam('type', null, null);
        $sportLeagueId = $this->dispatcher->getParam('league', null, null);
        $list = $this->getTeamRepo()->getList($sportTypeId, $sportLeagueId);

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

}
