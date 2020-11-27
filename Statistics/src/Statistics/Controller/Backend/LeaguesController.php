<?php

namespace Statistics\Controller\Backend;

use Articles\Entity\Category;
use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Statistics\Entity\SportLeague;
use Statistics\Entity\SportLeagueGroup;
use Statistics\Entity\SportType;
use Statistics\Forms\SportLeagueForm;

class LeaguesController extends AbstractBackendController
{

    /**
     * @var \Statistics\Repository\SportTypeRepository
     */
    protected $_typeRepo;

    /**
     * @var \Statistics\Repository\SportLeagueRepository
     */
    protected $_leagueRepo;

    /**
     * @var \Statistics\Repository\SportLeagueGroupRepository
     */
    protected $_leagueGroupRepo;

    /**
     * @var \Articles\Repository\CategoryRepository
     */
    protected $_categoriesRepo;

    /**
     * Sport leagues list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->leagues;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $options = [
            'sportTypes' => $this->getSportTypesOptionsList(),
        ];

        $qb = $this->getLeagueRepo()->createQueryBuilder('l')
                ->select('l, type, groups')
                ->leftJoin('l.sportType', 'type')
                ->leftJoin('l.groups', 'groups')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('l.id', 'ASC');

        $filters = new Filters($this->request);
        $filters
            ->addField('sportType', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('l.sportType = :sportType');
                    $qb->setParameter('sportType', $value);
                }
            })
            ->searchInFields('search', [ 
                'l.title', 'l.key',
            ])
        ;

        $filters->apply($qb, 'l');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('perPage', 'currentPage', 'options', 'paginator', 'filters'));
    }

    /**
     * Sport league add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $league = new SportLeague();
        $form = new SportLeagueForm($league, ['edit' => true]);
        $action = $this->url->get(['for' => 'sport_leagues_add']);
        $error  = '';

        if ($this->request->isPost()) {
            $form->bind($this->request->getPost(), $league);
            if ($form->isValid($this->request->getPost())) {
                try {
                    // Type
                    if ((int)$league->getSportType() > 0) {
                        $type = $this->getTypeRepo()->findObjectById($league->getSportType());
                        $league->setSportType($type);
                    } else {
                        $league->setSportType(null);
                    }
                    // Mapped article category
                    if ((int)$league->getArticleCategoryLvl2() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($league->getArticleCategoryLvl2());
                        $league->setArticleCategoryLvl2($category);
                    } else {
                        $league->setArticleCategoryLvl2(null);
                    }
                    // Groups
                    $options = $this->request->getPost()['options'];
                    foreach ($options as $x => $row) {
                        $group = new SportLeagueGroup();
                        $group->setTitle($row['title']);
                        $group->setKey($row['key']);
                        $group->setSportLeague($league);
                        $this->getEntityManager()->persist($group);
                    }
                    // Save data
                    $this->getEntityManager()->persist($league);
                    $this->getEntityManager()->flush();
                    // Back to list
                    $this->flashSession->success(sprintf('Sport league "%s" created successfully!', (string)$league));
                    return $this->response->redirect($this->url->get(['for' => 'sport_leagues_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('league', 'form', 'action', 'error'));
    }

    /**
     * Sport league edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $league = $this->getLeagueRepo()->findObjectById($id);
        $savedGroups = $league->getGroups();

        if (null === $league) {
            $this->flashSession->error(sprintf('Sport league with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'sport_leagues_list']));
        }

        $form = new SportLeagueForm($league, ['edit' => true]);
        $action = $this->url->get(['for' => 'sport_leagues_edit', 'id' => $league->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            $form->bind($this->request->getPost(), $league);
            if ($form->isValid($this->request->getPost())) {
                try {
                    // Type
                    if ((int)$league->getSportType() > 0) {
                        $type = $this->getTypeRepo()->findObjectById($league->getSportType());
                        $league->setSportType($type);
                    } else {
                        $league->setSportType(null);
                    }
                    // Mapped article category
                    if ((int)$league->getArticleCategoryLvl2() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($league->getArticleCategoryLvl2());
                        $league->setArticleCategoryLvl2($category);
                    } else {
                        $league->setArticleCategoryLvl2(null);
                    }
                    // Groups add/update
                    $ids = [];
                    $options = $this->request->getPost()['options'];
                    foreach ($options as $x => $row) {
                        if ($row['id'] === '') { // add new
                            $group = new SportLeagueGroup();
                            $group->setTitle($row['title']);
                            $group->setKey($row['key']);
                            $group->setSportLeague($league);
                            $this->getEntityManager()->persist($group);
                        } else { // update existing
                            foreach ($savedGroups as $savedGroup) {
                                if ((string)$savedGroup->getId() === (string)$row['id']) {
                                    $savedGroup->setTitle($row['title']);
                                    $savedGroup->setKey($row['key']);
                                    $ids[] = (string)$row['id'];
                                }
                            }
                        }
                    }
                    // Groups removing
                    foreach ($savedGroups as $groupToDelete) {
                        if (!in_array((string)$groupToDelete->getId(), $ids)) {
                            $league->removeGroup($groupToDelete);
                        }
                    }
                    // Save data
                    $this->getEntityManager()->persist($league);
                    $this->getEntityManager()->flush();
                    // Back to list
                    $this->flashSession->success(sprintf('Sport league "%s" info updated successfully!', (string)$league));
                    return $this->response->redirect($this->url->get(['for' => 'sport_leagues_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('league', 'form', 'action', 'error'));
    }

    /**
     * Get leagues list by sport type.
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
        $list = $this->getLeagueRepo()->getList($sportTypeId);

        $rows = [];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        $this->response->setJsonContent(['success' => 1, 'data' => $rows]);

        return $this->response->send();
    }

    /**
     * Get league groups list by sport league.
     * 
     * @access public
     * @return json
     */
    public function loadGroupsItemsJsonAction()
    {
        if ($this->request->isAjax() !== true) {
            $this->response->setJsonContent(['success' => 0, 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        $sportLeagueId = $this->dispatcher->getParam('league', null, null);
        $list = $this->getLeagueGroupRepo()->getList($sportLeagueId);

        $rows = [];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        $this->response->setJsonContent(['success' => 1, 'data' => $rows]);

        return $this->response->send();
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
     * Get Category entity repository
     * 
     * @access public
     * @return \Articles\Repository\CategoryRepository
     */
    public function getCategoryRepo()
    {
        if ($this->_categoriesRepo === null || !$this->_categoriesRepo) {
            $this->_categoriesRepo = $this->getEntityRepository(Category::class);
        }

        return $this->_categoriesRepo;
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

}
