<?php

namespace Forum\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Forum\Entity\ForumReply;
use Forum\Entity\ForumReportedReply;
use Forum\Entity\ForumTopic;
use Forum\Forms\ForumReplyForm;
use Forum\Tool\ForumReportedReplyState;

class RepliesController extends AbstractBackendController
{

    /**
     * @var \Forum\Repository\ForumReplyRepository
     */
    protected $_repliesRepo;

    /**
     * @var \Forum\Repository\ForumReportedReplyRepository
     */
    protected $_repliesReportedRepo;

    /**
     * Forum replies list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->replies;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getForumReplyRepo()->createQueryBuilder('c')
                ->select('c')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('c.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('blocked')
            ->addField('unchecked')
            ->addField('createdAtFrom', Filters::TYPE_DATE, 'c.createdAt', Filters::COMP_GTE)
            ->addField('createdAtTo', Filters::TYPE_DATE, 'c.createdAt', Filters::COMP_LTE)
            ->addField('rateAvgFrom', Filters::TYPE_INT, 'c.rateAvg', Filters::COMP_GTE)
            ->addField('rateAvgTo', Filters::TYPE_INT, 'c.rateAvg', Filters::COMP_LTE)
            ->searchInFields('search', [ 
                'c.content', 'c.ipAddress', 'c.sessionId'
            ])
        ;

        $filters->apply($qb, 'c');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * Forum reply edit view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $reply = $this->getForumReplyRepo()->findObjectById($id);

        if (null === $reply) {
            $this->flashSession->error(sprintf('Reply with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'forum_replies_list']));
        }

        $prevBlockedValue = $reply->getBlocked();

        if ($reply->isUnchecked()) {
            $reply->setUnchecked(false);
            $this->getEntityManager()->persist($reply);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->refresh($reply);
        }

        $form = new ForumReplyForm($reply, ['edit' => true]);
        $action = $this->url->get(['for' => 'forum_replies_edit', 'id' => $reply->getId()]);
        $error  = '';

        $updateCategories = false;

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $reply);

                $topic = $reply->getTopic();
                /* @var $topic ForumTopic */

                if (is_object($topic)) {
                    if ($prevBlockedValue != $reply->getBlocked()) {
                        if ((int)$reply->getBlocked() === 1) {
                            $topic->decreaseCommentsCount();
                        } else {
                            $topic->increaseCommentsCount();
                        }
                        $updateCategories = true;
                    }
                }

                $this->getEntityManager()->flush();
                
                if ($updateCategories) {
                    $this->recheckCategoriesData();
                }

                $this->flashSession->success(sprintf('Reply "%s" info updated successfully!', (string)$reply));
                return $this->response->redirect($this->url->get(['for' => 'forum_replies_list']));
            }
        }

        $this->view->setVars(compact('reply', 'form', 'action', 'error'));
    }

    /**
     * Forum reported replies list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function reportedAction()
    {
        $perPage = $this->config->settings->page_size->replies;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getForumReportedReplyRepo()->createQueryBuilder('c')
                ->select('c')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('c.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('state')
            ->addField('createdAtFrom', Filters::TYPE_DATE, 'c.createdAt', Filters::COMP_GTE)
            ->addField('createdAtTo', Filters::TYPE_DATE, 'c.createdAt', Filters::COMP_LTE)
            ->searchInFields('search', [ 
                'c.ipAddress', 'c.sessionId'
            ])
        ;

        $filters->apply($qb, 'c');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters'));
    }

    public function reportedAcceptAction()
    {
        $id = $this->dispatcher->getParam('id');
        $reported = $this->getForumReportedReplyRepo()->find($id);

        if (null === $reported) {
            $this->flashSession->error(sprintf('Report with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'forum_reported_replies_list']));
        }

        try {
            $reply = $reported->getReply();
            /* @var $reply ForumReply */

            $prevBlockedValue = $reply->getBlocked();

            $reply->setBlocked(true);
            $this->getEntityManager()->persist($reply);

            $reported->setState(ForumReportedReplyState::STATE_ACCEPTED);
            $this->getEntityManager()->persist($reported);

            if ($prevBlockedValue != $reply->getBlocked()) {
                $topic = $reply->getTopic();
                /* @var $topic ForumTopic */

                if (is_object($topic)) {
                    if ((int)$reply->getBlocked() === 1) {
                        $topic->decreaseCommentsCount();
                        $updateCategories = true;
                    }
                }
            }

            $this->getEntityManager()->flush();

            if ($updateCategories) {
                $this->recheckCategoriesData();
            }

            $this->flashSession->success(sprintf('Report "%s" updated as accepted and reply blocked successfully!', (string)$reported));
        } catch (\Exception $exc) {
            $this->flashSession->error(sprintf('Error: %s', $exc->getMessage()));
        }

        return $this->response->redirect($this->url->get(['for' => 'forum_reported_replies_list']));
    }

    public function reportedIgnoreAction()
    {
        $id = $this->dispatcher->getParam('id');
        $reported = $this->getForumReportedReplyRepo()->find($id);

        if (null === $reported) {
            $this->flashSession->error(sprintf('Report with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'forum_reported_replies_list']));
        }

        try {
            $reported->setState(ForumReportedReplyState::STATE_IGNORED);
            $this->getEntityManager()->persist($reported);
            $this->getEntityManager()->flush();

            $this->flashSession->success(sprintf('Report "%s" updated as ignored successfully!', (string)$reported));
        } catch (\Exception $exc) {
            $this->flashSession->error(sprintf('Error: %s', $exc->getMessage()));
        }

        return $this->response->redirect($this->url->get(['for' => 'forum_reported_replies_list']));
    }

    /**
     * @return void
     */
    protected function recheckCategoriesData()
    {
        // @toDo
    }

    /**
     * Get ForumReply entity repository
     * 
     * @access public
     * @return \Forum\Repository\ForumReplyRepository
     */
    public function getForumReplyRepo()
    {
        if ($this->_repliesRepo === null || !$this->_repliesRepo) {
            $this->_repliesRepo = $this->getEntityRepository(ForumReply::class);
        }

        return $this->_repliesRepo;
    }

    /**
     * Get ForumReportedReply entity repository
     * 
     * @access public
     * @return \Forum\Repository\ForumReportedReplyRepository
     */
    public function getForumReportedReplyRepo()
    {
        if ($this->_repliesReportedRepo === null || !$this->_repliesReportedRepo) {
            $this->_repliesReportedRepo = $this->getEntityRepository(ForumReportedReply::class);
        }

        return $this->_repliesReportedRepo;
    }

}
