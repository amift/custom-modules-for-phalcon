<?php

namespace Common\Controller;

use Common\Controller\AbstractGlobalController;
use Phalcon\Mvc\Dispatcher;

class AbstractBackendController extends AbstractGlobalController
{

    /**
     * Execute before the router.
     * So we can determine if this is a private controller, 
     * and must be authenticated, or a public controller that is open to all.
     * 
     * @access public
     * @param Dispatcher $dispatcher
     * @return boolean
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        // Get the current identity
        $identity = $this->auth->getIdentity();

        // Check if user is loggged in
        if ($identity === null && !$this->isLoginPage($dispatcher)) {

            // Get current requested url
            $redirect = trim($this->request->get('_url'), '/');
            if ($redirect !== '') {
                $redirect = '?redirect=/' . $redirect;
            }

            // Redirect to login page
            $this->response->redirect('login' . $redirect);

            return false;
        }

        // Check if logged in user is on login page
        if (is_array($identity) && $this->isLoginPage($dispatcher)) {
            $this->response->redirect('');
        }

        // Get requested controller and action names
        $controllerName = $dispatcher->getControllerName();
        $actionName     = $dispatcher->getActionName();

        // Only check permissions on private controllers
        if ($this->acl->isPrivate($controllerName, $actionName)) {

            // Check if the user have permission to the current option
            if (!isset($identity['profile']) || !$this->acl->isAllowed($identity['profile'], $controllerName, $actionName)) {
                //$this->flash->notice(sprintf("You don\'t have access to this module: %s:%s", $controllerName, $actionName));
                $dispatcher->forward([
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\Backend',
                    'controller'    => 'error',
                    'action'        => 'noPermission',
                ]);
                return false;
            }
        }
    }

    /**
     * Check if current route is login page.
     * 
     * @access protected
     * @param Dispatcher $dispatcher
     * @return boolean
     */
    protected function isLoginPage(Dispatcher $dispatcher)
    {
        if (
            $dispatcher->getControllerName() === 'user' && 
            $dispatcher->getActionName() === 'login'
        ) {
            return true;
        }

        return false;
    }

}
