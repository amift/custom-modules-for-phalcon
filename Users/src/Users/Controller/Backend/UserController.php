<?php

namespace Users\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Users\Entity\User;
use Users\Forms\LoginForm;
use Users\Forms\ProfileChangePasswordForm;

class UserController extends AbstractBackendController
{

    /**
     * Backend user login form
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function loginAction()
    {
        $form   = new LoginForm();
        $action = 'login?redirect=' . $this->request->getQuery('redirect', 'string', '');
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                try {
                    $this->auth->check([
                        'email'     => $this->request->getPost('email', 'email', ''),
                        'password'  => $this->request->getPost('password', 'string', '')
                    ]);

                    return $this->response->redirect($this->request->getQuery('redirect', 'string', ''));
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('form', 'action', 'error'));
        $this->view->setLayout('login');
    }

    /**
     * Backend user logout action
     *
     * @access public
     * @return void
     */
    public function logoutAction()
    {
        $this->auth->remove();

        return $this->response->redirect('login');
    }

    /**
     * Backend user profile form
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function profileAction()
    {
        $user = $this->auth->getAuthorisedUser();
        /* @var $user User */

        $contentClass = 'users_page';
        $form         = new ProfileChangePasswordForm($user, []);
        $action       = $this->url->get('profile');

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                try {
                    $user = $this->di->get('user_service')->changePassword($user, $this->request->getPost());
                    $this->flashSession->success(sprintf('Password changed successfully!'));
                    return $this->response->redirect('profile');
                } catch (\Exception $exc) {
                    $error = $exc->getMessage();
                }
            }
        }

        $this->view->setVars(compact('user', 'contentClass', 'form', 'action', 'error'));
    }

}
