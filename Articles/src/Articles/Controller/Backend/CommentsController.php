<?php

namespace Articles\Controller\Backend;

use Articles\Entity\Article;
use Articles\Entity\Comment;
use Articles\Entity\ReportedComment;
use Articles\Forms\CommentForm;
use Articles\Tool\ReportState;
use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Polls\Entity\Poll;

class CommentsController extends AbstractBackendController
{

    /**
     * @var \Articles\Repository\CommentRepository
     */
    protected $_commentRepo;

    /**
     * @var \Articles\Repository\ReportedCommentRepository
     */
    protected $_reportedCommentRepo;

    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->comments;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getCommentRepo()->createQueryBuilder('c')
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

    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $comment = $this->getCommentRepo()->findObjectById($id);

        if (null === $comment) {
            $this->flashSession->error(sprintf('Comment with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'all_comments_list']));
        }
        $prevBlockedValue = $comment->getBlocked();
        if ($comment->isUnchecked()) {
            $comment->setUnchecked(false);
            $this->getEntityManager()->persist($comment);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->refresh($comment);
        }
    
        $form = new CommentForm($comment, ['edit' => true]);
        $action = $this->url->get(['for' => 'comment_edit', 'id' => $comment->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $comment);

                $article = $comment->getArticle();
                /* @var $article Article */

                if (is_object($article)) {
                    if ($prevBlockedValue != $comment->getBlocked()) {
                        if ((int)$comment->getBlocked() === 1) {
                            $article->decreaseCommentsCount();
                        } else {
                            $article->increaseCommentsCount();
                        }
                    }
                }

                $poll = $comment->getPoll();
                /* @var $poll Poll */

                if (is_object($poll)) {
                    if ($prevBlockedValue != $comment->getBlocked()) {
                        if ((int)$comment->getBlocked() === 1) {
                            $poll->decreaseCommentsCount();
                        } else {
                            $poll->increaseCommentsCount();
                        }
                    }
                }

                $this->getEntityManager()->flush();

                $this->flashSession->success(sprintf('Comment "%s" info updated successfully!', (string)$comment));
                return $this->response->redirect($this->url->get(['for' => 'all_comments_list']));
            }
        }

        $this->view->setVars(compact('comment', 'form', 'action', 'error'));
    }

    /**
     * Get Comment entity repository
     * 
     * @access public
     * @return \Articles\Repository\CommentRepository
     */
    public function getCommentRepo()
    {
        if ($this->_commentRepo === null || !$this->_commentRepo) {
            $this->_commentRepo = $this->getEntityRepository(Comment::class);
        }

        return $this->_commentRepo;
    }

    public function reportedAction()
    {
        $perPage = $this->config->settings->page_size->comments;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getReportedCommentRepo()->createQueryBuilder('c')
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
        $reported = $this->getReportedCommentRepo()->find($id);

        if (null === $reported) {
            $this->flashSession->error(sprintf('Report with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'reported_comments_list']));
        }

        try {
            $comment = $reported->getComment();
            /* @var $comment Comment */

            $prevBlockedValue = $comment->getBlocked();

            $comment->setBlocked(true);
            $this->getEntityManager()->persist($comment);

            $reported->setState(ReportState::STATE_ACCEPTED);
            $this->getEntityManager()->persist($reported);

            if ($prevBlockedValue != $comment->getBlocked()) {
                $article = $comment->getArticle();
                /* @var $article Article */

                if (is_object($article)) {
                    if ((int)$comment->getBlocked() === 1) {
                        $article->decreaseCommentsCount();
                    }
                }

                $poll = $comment->getPoll();
                /* @var $poll Poll */

                if (is_object($poll)) {
                    if ((int)$comment->getBlocked() === 1) {
                        $poll->decreaseCommentsCount();
                    }
                }
            }

            $this->getEntityManager()->flush();

            $this->flashSession->success(sprintf('Report "%s" updated as accepted and comment blocked successfully!', (string)$reported));
        } catch (\Exception $exc) {
            $this->flashSession->error(sprintf('Error: %s', $exc->getMessage()));
        }

        return $this->response->redirect($this->url->get(['for' => 'reported_comments_list']));
    }

    public function reportedIgnoreAction()
    {
        $id = $this->dispatcher->getParam('id');
        $reported = $this->getReportedCommentRepo()->find($id);

        if (null === $reported) {
            $this->flashSession->error(sprintf('Report with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'reported_comments_list']));
        }

        try {
            $reported->setState(ReportState::STATE_IGNORED);
            $this->getEntityManager()->persist($reported);
            $this->getEntityManager()->flush();

            $this->flashSession->success(sprintf('Report "%s" updated as ignored successfully!', (string)$reported));
        } catch (\Exception $exc) {
            $this->flashSession->error(sprintf('Error: %s', $exc->getMessage()));
        }

        return $this->response->redirect($this->url->get(['for' => 'reported_comments_list']));
    }

    /**
     * Get ReportedComment entity repository
     * 
     * @access public
     * @return \Articles\Repository\ReportedCommentRepository
     */
    public function getReportedCommentRepo()
    {
        if ($this->_reportedCommentRepo === null || !$this->_reportedCommentRepo) {
            $this->_reportedCommentRepo = $this->getEntityRepository(ReportedComment::class);
        }

        return $this->_reportedCommentRepo;
    }

}