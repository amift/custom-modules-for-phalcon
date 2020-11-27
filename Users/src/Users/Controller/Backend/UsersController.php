<?php

namespace Users\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Users\Entity\User;
use Users\Forms\UserAddForm;
use Users\Forms\UserEditForm;
use Users\Forms\UserPasswordChangeForm;

class UsersController extends AbstractBackendController
{

    /**
     * Users list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $repo = $this->getEntityManager()->getRepository(User::class);
        $perPage = 20;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $repo->createQueryBuilder('u')
                ->select('u')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('u.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('state')
            ->searchInFields('search', [ 
                'u.email', 'u.phone', 'CONCAT(u.firstName, \' \', u.lastName)',
            ])
        ;

        $filters->apply($qb, 'u');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * User add (create) view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $form   = new UserAddForm();
        $action = $this->url->get(['for' => 'users_add']);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                try {
                    $user = $this->di->get('user_service')->createUser($this->request->getPost());
                    if (is_object($user)) {
                        $this->flashSession->success(sprintf('User "%s" created successfully!', (string)$user));
                        return $this->response->redirect($this->url->get(['for' => 'users_list']));
                    }
                    $error = "Error! Something gone wrong, please, try again.";
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('form', 'action', 'error'));
    }

    /**
     * User information view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function viewAction()
    {
        $id = $this->dispatcher->getParam('id');
        $user = $this->di->get('user_service')->getById($id);

        if (null === $user) {
            $this->flashSession->error(sprintf('User with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'users_list']));
        }

        $form   = new UserEditForm($user, ['edit' => true]);
        $action = $this->url->get(['for' => 'users_view', 'id' => $user->getId()]);
        $isEdit = false;
        $tab    = 'general';

        $this->view->setVars(compact('user', 'form', 'action', 'isEdit', 'tab'));
    }

    /**
     * User edit view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id = $this->dispatcher->getParam('id');
        $user = $this->di->get('user_service')->getById($id);

        if (null === $user) {
            $this->flashSession->error(sprintf('User with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'users_list']));
        }

        $form   = new UserEditForm($user, ['edit' => true]);
        $action = $this->url->get(['for' => 'users_edit', 'id' => $user->getId()]);
        $error  = '';
        $isEdit = true;
        $tab    = 'general';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                try {
                    $user = $this->di->get('user_service')->updateUser($user, $this->request->getPost());
                    $this->flashSession->success(sprintf('User "%s" general info updated successfully!', (string)$user));
                    return $this->response->redirect($this->url->get(['for' => 'users_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('user', 'form', 'action', 'error', 'isEdit', 'tab'));
    }

    /**
     * User password change view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function passwordAction()
    {
        $id = $this->dispatcher->getParam('id');
        $user = $this->di->get('user_service')->getById($id);

        if (null === $user) {
            $this->flashSession->error(sprintf('User with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'users_list']));
        }

        $form   = new UserPasswordChangeForm($user, ['edit' => true]);
        $action = $this->url->get(['for' => 'users_password', 'id' => $user->getId()]);
        $error  = '';
        $isEdit = false;
        $tab    = 'password';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                try {
                    $user = $this->di->get('user_service')->changePassword($user, $this->request->getPost());
                    $this->flashSession->success(sprintf('User "%s" password changed successfully!', (string)$user));
                    return $this->response->redirect($this->url->get(['for' => 'users_list']));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('user', 'form', 'action', 'error', 'isEdit', 'tab'));
    }

}
