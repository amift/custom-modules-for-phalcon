<?php

namespace Members\Controller\Backend;

use Articles\Entity\Article;
use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Communication\Entity\Notification;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Members\Entity\FailedLogin;
use Members\Entity\Member;
use Members\Entity\SuccessLogin;
use Members\Entity\Withdraws;
use Members\Forms\MemberEditForm;

class MembersController extends AbstractBackendController
{

    /**
     * @var \Articles\Repository\ArticleRepository
     */
    protected $_articlesRepo;

    /**
     * @var \Communication\Repository\NotificationRepository
     */
    protected $_notificationsRepo;

    /**
     * @var \Members\Repository\FailedLoginRepository
     */
    protected $_authFailedRepo;

    /**
     * @var \Members\Repository\SuccessLoginRepository
     */
    protected $_authSuccessRepo;

    /**
     * @var \Members\Repository\MemberRepository
     */
    protected $_membersRepo;

    /**
     * @var \Members\Repository\WithdrawsRepository
     */
    protected $_withdrawsRepo;

    /**
     * Members list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->members;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getMemberRepo()->createQueryBuilder('m')
                ->select('m')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('m.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('state')
            ->addField('confirmed')
            ->addField('hasArticles', Filters::TYPE_CALLBACK, function($qb, $value){
                if ($value !== '') {
                    switch ((int)$value) {
                        case 0 :
                            //$qb->andWhere('m.todo');
                            break;
                        case 1 :
                            //$qb->andWhere('m.todo');
                            break;
                    }
                }
            })
            ->searchInFields('search', [ 
                'm.email', 'm.username', 'm.createdFromIp', 'm.loginLastIp'
            ])
        ;

        $filters->apply($qb, 'm');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * Member overview info view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function viewAction()
    {
        $id     = $this->dispatcher->getParam('id');
        $member = $this->getMemberRepo()->findObjectById($id);
        $tab    = 'general';

        if (null === $member) {
            $this->flashSession->error(sprintf('Member with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'members_list']));
        }

        $this->view->setVars(compact('member', 'tab'));
    }

    /**
     * Member edit view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id     = $this->dispatcher->getParam('id');
        $member = $this->getMemberRepo()->findObjectById($id);
        $tab    = 'edit';

        if (null === $member) {
            $this->flashSession->error(sprintf('Member with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'members_list']));
        }

        $form   = new MemberEditForm($member, ['edit' => true]);
        $action = $this->url->get(['for' => 'members_edit', 'id' => $member->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $member);
                try {
                    $canSave = true;
                    if ($member->isBanned() === true) {
                        
                        if ($member->isBannedPosting() || $member->isBannedCommenting()) {
                            $canSave = true;
                        } else {
                            $canSave = false;
                            $this->flashSession->error(sprintf('For status "Banned" nead at least one banned reason to select!'));
                        }
                    } else {
                        $member->setBannedPosting(false);
                        $member->setBannedCommenting(false);
                    }

                    if ($canSave === true) {
                        // Save data
                        $this->getEntityManager()->persist($member);
                        $this->getEntityManager()->flush();

                        // Back to list
                        $this->flashSession->success(sprintf('Member "%s" info updated successfully!', (string)$member));
                        return $this->response->redirect($this->url->get(['for' => 'members_list']));
                    } else {
                        return $this->response->redirect($this->url->get(['for' => 'members_edit', 'id' => $member->getId()]));
                    }

                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $form->get('confirmed')->setDefault($member->isConfirmed() ? 1 : 0);

        $this->view->setVars(compact('member', 'tab', 'form', 'action', 'error'));
    }

    /**
     * Member articles log info view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function articlesAction()
    {
        $id     = $this->dispatcher->getParam('id');
        $member = $this->getMemberRepo()->findObjectById($id);
        $tab    = 'articles';

        if (null === $member) {
            $this->flashSession->error(sprintf('Member with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'members_list']));
        }

        $perPage = $this->config->settings->page_size->tab_list;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getArticleRepo()->createQueryBuilder('a')
                ->select('a')
                ->where('a.member = :id')
                ->setParameter('id', $id)
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('a.id', 'DESC');

        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('member', 'tab', 'paginator', 'perPage', 'currentPage'));
    }

    /**
     * Member withdraws log info view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function withdrawsAction()
    {
        $id     = $this->dispatcher->getParam('id');
        $member = $this->getMemberRepo()->findObjectById($id);
        $tab    = 'withdraws';

        if (null === $member) {
            $this->flashSession->error(sprintf('Member with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'members_list']));
        }

        $perPage = $this->config->settings->page_size->tab_list;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getWithdrawsRepo()->createQueryBuilder('w')
                ->select('w, m')
                ->leftJoin('w.member', 'm')
                ->where('w.member = :id')
                ->setParameter('id', $id)
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('w.id', 'DESC');

        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('member', 'tab', 'paginator', 'perPage', 'currentPage'));
    }

    /**
     * Member communication log info view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function communicationAction()
    {
        $id     = $this->dispatcher->getParam('id');
        $member = $this->getMemberRepo()->findObjectById($id);
        $tab    = 'communication';

        if (null === $member) {
            $this->flashSession->error(sprintf('Member with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'members_list']));
        }

        $perPage = $this->config->settings->page_size->tab_list;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getNotificationRepo()->createQueryBuilder('n')
                ->select('n')
                ->where('n.member = :id')
                ->setParameter('id', $id)
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('n.id', 'DESC');

        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('member', 'tab', 'paginator', 'perPage', 'currentPage'));
    }

    /**
     * Member authorisations log info view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function authorisationsAction()
    {
        $id     = $this->dispatcher->getParam('id');
        $group  = $this->dispatcher->getParam('group', 'string', 'success');
        $member = $this->getMemberRepo()->findObjectById($id);
        $tab    = 'authorisations';

        if (null === $member) {
            $this->flashSession->error(sprintf('Member with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'members_list']));
        }

        if (!in_array($group, ['success', 'failed'])) {
            $this->flashSession->error(sprintf('Invalid authorisation group "%s" requested', $group));
            return $this->response->redirect($this->url->get(['for' => 'members_list']));
        }
        
        $groups = [
            [ 'group' => 'success', 'title' => 'Success', 'selected' => ($group === 'success' ? true : false) ],
            [ 'group' => 'failed', 'title' => 'Failed', 'selected' => ($group === 'failed' ? true : false) ],
        ];

        $perPage = $this->config->settings->page_size->tab_list;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $repo = $group === 'failed' ? $this->getAuthFailedRepo() : $this->getAuthSuccessRepo();

        $qb = $repo->createQueryBuilder('a')
                ->select('a')
                ->where('a.member = :id')
                ->setParameter('id', $id)
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('a.id', 'DESC');

        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('member', 'tab', 'group', 'groups', 'paginator', 'perPage', 'currentPage'));
    }

    /**
     * Get Article entity repository
     * 
     * @access public
     * @return \Articles\Repository\ArticleRepository
     */
    public function getArticleRepo()
    {
        if ($this->_articlesRepo === null || !$this->_articlesRepo) {
            $this->_articlesRepo = $this->getEntityRepository(Article::class);
        }

        return $this->_articlesRepo;
    }

    /**
     * Get Notification entity repository
     * 
     * @access public
     * @return \Communication\Repository\NotificationRepository
     */
    public function getNotificationRepo()
    {
        if ($this->_notificationsRepo === null || !$this->_notificationsRepo) {
            $this->_notificationsRepo = $this->getEntityRepository(Notification::class);
        }

        return $this->_notificationsRepo;
    }

    /**
     * Get FailedLogin entity repository
     * 
     * @access public
     * @return \Members\Repository\FailedLoginRepository
     */
    public function getAuthFailedRepo()
    {
        if ($this->_authFailedRepo === null || !$this->_authFailedRepo) {
            $this->_authFailedRepo = $this->getEntityRepository(FailedLogin::class);
        }

        return $this->_authFailedRepo;
    }

    /**
     * Get SuccessLogin entity repository
     * 
     * @access public
     * @return \Members\Repository\SuccessLoginRepository
     */
    public function getAuthSuccessRepo()
    {
        if ($this->_authSuccessRepo === null || !$this->_authSuccessRepo) {
            $this->_authSuccessRepo = $this->getEntityRepository(SuccessLogin::class);
        }

        return $this->_authSuccessRepo;
    }

    /**
     * Get Member entity repository
     * 
     * @access public
     * @return \Members\Repository\MemberRepository
     */
    public function getMemberRepo()
    {
        if ($this->_membersRepo === null || !$this->_membersRepo) {
            $this->_membersRepo = $this->getEntityRepository(Member::class);
        }

        return $this->_membersRepo;
    }

    /**
     * Get Withdraws entity repository
     * 
     * @access public
     * @return \Members\Repository\WithdrawsRepository
     */
    public function getWithdrawsRepo()
    {
        if ($this->_withdrawsRepo === null || !$this->_withdrawsRepo) {
            $this->_withdrawsRepo = $this->getEntityRepository(Withdraws::class);
        }

        return $this->_withdrawsRepo;
    }

}
