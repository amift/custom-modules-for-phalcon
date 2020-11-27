<?php

namespace Statistics\Controller\Backend;

use Articles\Entity\Category;
use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Statistics\Entity\SportType;
use Statistics\Forms\SportTypeForm;

class TypesController extends AbstractBackendController
{

    /**
     * @var \Statistics\Repository\SportTypeRepository
     */
    protected $_typeRepo;

    /**
     * @var \Articles\Repository\CategoryRepository
     */
    protected $_categoriesRepo;

    /**
     * Sport types list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->types;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getTypeRepo()->createQueryBuilder('t')
                ->select('t, leagues')
                ->leftJoin('t.leagues', 'leagues')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('t.id', 'ASC');

        $filters = new Filters($this->request);
        $filters
            ->searchInFields('search', [ 
                't.title', 't.key',
            ])
        ;

        $filters->apply($qb, 't');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('perPage', 'currentPage', 'paginator', 'filters'));
    }

    /**
     * Sport type add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $type = new SportType();
        $form = new SportTypeForm($type, ['edit' => true]);
        $action = $this->url->get(['for' => 'sport_types_add']);
        $error  = '';

        if ($this->request->isPost()) {
            $form->bind($this->request->getPost(), $type);
            if ($form->isValid($this->request->getPost())) {
                try {
                    // Mapped article category
                    if ((int)$type->getArticleCategoryLvl1() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($type->getArticleCategoryLvl1());
                        $type->setArticleCategoryLvl1($category);
                    } else {
                        $type->setArticleCategoryLvl1(null);
                    }
                    // Save data
                    $this->getEntityManager()->persist($type);
                    $this->getEntityManager()->flush();
                    // Back to list
                    $this->flashSession->success(sprintf('Sport type "%s" created successfully!', (string)$type));
                    return $this->response->redirect($this->url->get(['for' => 'sport_types_list']));

                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('type', 'form', 'action', 'error'));
    }

    /**
     * Sport type edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $type = $this->getTypeRepo()->findObjectById($id);

        if (null === $type) {
            $this->flashSession->error(sprintf('Sport type with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'sport_types_list']));
        }

        $form = new SportTypeForm($type, ['edit' => true]);
        $action = $this->url->get(['for' => 'sport_types_edit', 'id' => $type->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            $form->bind($this->request->getPost(), $type);
            if ($form->isValid($this->request->getPost())) {
                try {
                    // Mapped article category
                    if ((int)$type->getArticleCategoryLvl1() > 0) {
                        $category = $this->getCategoryRepo()->findObjectById($type->getArticleCategoryLvl1());
                        $type->setArticleCategoryLvl1($category);
                    } else {
                        $type->setArticleCategoryLvl1(null);
                    }
                    // Save data
                    $this->getEntityManager()->persist($type);
                    $this->getEntityManager()->flush();
                    // Back to list
                    $this->flashSession->success(sprintf('Sport type "%s" info updated successfully!', (string)$type));
                    return $this->response->redirect($this->url->get(['for' => 'sport_types_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('type', 'form', 'action', 'error'));
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

    public function articlesCategoriesLoadJsonAction()
    {
        if ($this->request->isAjax() !== true) {
            $this->response->setJsonContent(['success' => 0, 'errors' => 'Invalid access']);
            return $this->response->send();
        }

        $loadAll = true;

        $typeId = $this->dispatcher->getParam('id');
        $type = $this->getTypeRepo()->findObjectById($typeId);
        if (is_object($type)) {
            $parent = $type->getArticleCategoryLvl1();
            if (is_object($parent)) {
                $loadAll = false;
                $list = $this->getCategoryRepo()->getParentChildsList($parent->getId());
            }
        }

        if ($loadAll) {
            $list = $this->getCategoryRepo()->getParentsListFromLevel(2);
        }

        $rows = [];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        $this->response->setJsonContent(['success' => 1, 'data' => $rows]);

        return $this->response->send();
    }

}
