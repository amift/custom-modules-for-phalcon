<?php

namespace Forum\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Common\Tool\StringTool;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Forum\Entity\ForumTopic;
use Forum\Entity\ForumCategory;
use Forum\Forms\ForumTopicForm;
use Forum\Tool\ForumTopicState;

class TopicsController extends AbstractBackendController
{

    /**
     * @var \Forum\Repository\ForumCategoryRepository
     */
    protected $_categoriesRepo;

    /**
     * @var \Forum\Repository\ForumTopicRepository
     */
    protected $_topicsRepo;

    /**
     * Forum topics list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->topics;
        $currentPage = $this->request->getQuery('page', 'int', 1);
        $categories = $this->getCategoriesOptionsFiltersList();

        $qb = $this->getForumTopicRepo()->createQueryBuilder('a')
                ->select('a')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('a.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('state')
            ->addField('pinned')
            ->addField('hot')
            ->addField('locked')
            ->addField('createdAtFrom', Filters::TYPE_DATE, 'a.createdAt', Filters::COMP_GTE)
            ->addField('createdAtTo', Filters::TYPE_DATE, 'a.createdAt', Filters::COMP_LTE)
            ->addField('member')
            ->addField('categoryLvl1', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('a.categoryLvl1 = :categoryLvl1');
                    $qb->setParameter('categoryLvl1', $value);
                }
            })
            ->addField('categoryLvl2', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('a.categoryLvl2 = :categoryLvl2');
                    $qb->setParameter('categoryLvl2', $value);
                }
            })
            ->addField('categoryLvl3', Filters::TYPE_CALLBACK, function($qb, $value){
                if ((int)$value > 0) {
                    $qb->andWhere('a.categoryLvl3 = :categoryLvl3');
                    $qb->setParameter('categoryLvl3', $value);
                }
            })
            ->searchInFields('search', [ 
                'a.title', 'a.slug', 'a.content',
            ])
        ;

        $filters->apply($qb, 'a');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters', 'categories'));
    }

    /**
     * Forum topic edit view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $topic = $this->getForumTopicRepo()->findObjectById($id);

        $oldCategories = [
            'cat1' => is_object($topic->getCategoryLvl1()) ? $topic->getCategoryLvl1()->getId() : null,
            'cat2' => is_object($topic->getCategoryLvl2()) ? $topic->getCategoryLvl2()->getId() : null,
            'cat3' => is_object($topic->getCategoryLvl3()) ? $topic->getCategoryLvl3()->getId() : null,
        ];

        $newCategories = [
            'cat1' => null,
            'cat2' => null,
            'cat3' => null,
        ];
        
        $oldStatus = $topic->getState();

        if (null === $topic) {
            $this->flashSession->error(sprintf('Forum topic with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'forum_topics_list']));
        }

        $form = new ForumTopicForm($topic, ['edit' => true]);
        $action = $this->url->get(['for' => 'forum_topics_edit', 'id' => $topic->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $topic);

                if ((int)$topic->getCategoryLvl1() > 0) {
                    $category = $this->getForumCategoryRepo()->findObjectById($topic->getCategoryLvl1());
                    $topic->setCategoryLvl1($category);
                    $newCategories['cat1'] = $category->getId();
                } else {
                    $topic->setCategoryLvl1(null);
                }

                if ((int)$topic->getCategoryLvl2() > 0) {
                    $category = $this->getForumCategoryRepo()->findObjectById($topic->getCategoryLvl2());
                    $topic->setCategoryLvl2($category);
                    $newCategories['cat2'] = $category->getId();
                } else {
                    $topic->setCategoryLvl2(null);
                }

                if ((int)$topic->getCategoryLvl3() > 0) {
                    $category = $this->getForumCategoryRepo()->findObjectById($topic->getCategoryLvl3());
                    $topic->setCategoryLvl3($category);
                    $newCategories['cat3'] = $category->getId();
                } else {
                    $topic->setCategoryLvl3(null);
                }

                $topic->setContent(StringTool::createParagraphTags($topic->getContent()));

                // Save data
                $this->getEntityManager()->persist($topic);
                $this->getEntityManager()->flush();

                // Recheck if need to update categories data 
                $this->getEntityManager()->refresh($topic);
                $newStatus = $topic->getState();
                $hasCategoryChanges = false;
                if ($oldCategories['cat3'] !== $newCategories['cat3']) {
                    $hasCategoryChanges = true;
                }
                if ($oldCategories['cat2'] !== $newCategories['cat2']) {
                    $hasCategoryChanges = true;
                }
                if ($oldCategories['cat1'] !== $newCategories['cat1']) {
                    $hasCategoryChanges = true;
                }
                if (!$hasCategoryChanges && $oldStatus !== $newStatus) {
                    if (in_array($oldStatus, ForumTopicState::getOutputStates()) && in_array($newStatus, ForumTopicState::getHideStates())) {
                        $hasCategoryChanges = true;
                    } elseif (in_array($oldStatus, ForumTopicState::getHideStates()) && in_array($newStatus, ForumTopicState::getOutputStates())) {
                        $hasCategoryChanges = true;
                    }
                }
                if ($hasCategoryChanges) {
                    $this->recheckCategoriesData();
                }

                // Back to list
                $this->flashSession->success(sprintf('Forum topic "%s" info updated successfully!', (string)$topic));
                return $this->response->redirect($this->url->get(['for' => 'forum_topics_list']));
            }
        }

        $this->view->setVars(compact('topic', 'form', 'action', 'error'));
    }

    /**
     * @return array
     */
    protected function getCategoriesOptionsFiltersList()
    {
        return [
            'categoryLvl1' => $this->getCategoriesOptionsListByLevelAndParent(1),
            'categoryLvl2' => $this->getCategoriesOptionsListByLevelAndParent(2, $this->request->getQuery('categoryLvl1', null, null)),
            'categoryLvl3' => $this->getCategoriesOptionsListByLevelAndParent(3, $this->request->getQuery('categoryLvl2', null, null))
        ];
    }

    /**
     * @return array
     */
    protected function getCategoriesOptionsListByLevelAndParent($level = 1, $parentId = null)
    {
        $list = $this->getForumCategoryRepo()->getParentsListFromLevel($level, $parentId);

        $rows = [];
        foreach ($list as $row) {
            $rows[$row['id']] = sprintf('%s', $row['title']);
        }

        return $rows;
    }

    /**
     * @return void
     */
    protected function recheckCategoriesData()
    {
        // @toDo
    }

    /**
     * Get ForumCategory entity repository
     * 
     * @access public
     * @return \Forum\Repository\ForumCategoryRepository
     */
    public function getForumCategoryRepo()
    {
        if ($this->_categoriesRepo === null || !$this->_categoriesRepo) {
            $this->_categoriesRepo = $this->getEntityRepository(ForumCategory::class);
        }

        return $this->_categoriesRepo;
    }

    /**
     * Get ForumTopic entity repository
     * 
     * @access public
     * @return \Forum\Repository\ForumTopicRepository
     */
    public function getForumTopicRepo()
    {
        if ($this->_topicsRepo === null || !$this->_topicsRepo) {
            $this->_topicsRepo = $this->getEntityRepository(ForumTopic::class);
        }

        return $this->_topicsRepo;
    }

}
