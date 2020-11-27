<?php

namespace Common\Controller;

use Common\Controller\AbstractGlobalController;
use Phalcon\Mvc\Dispatcher;

class AbstractFrontendController extends AbstractGlobalController
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
        if ($this->config->maintanence === true) {
            $dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'maintanence',
                'action'        => 'technicalWorks',
            ]);
            return false;
        }

        if ($this->config->coming_soon === true) {
            $dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'maintanence',
                'action'        => 'comingSoon',
            ]);
            return false;
        }
      
        $controllerName = $dispatcher->getControllerName();
        $actionName = $dispatcher->getActionName();
        
        $uri = $this->request->getURI();
        $url = rtrim($uri, ' /');
        if (strlen($uri) > 1) {
            if ($uri !== $url) {
                return $this->response->redirect($url, true, 301);
            }
        }

        // Only check permissions on private controllers
        if ($this->acl->isPrivate($controllerName, $actionName)) {

            // Get the current identity
            $identity = $this->auth->getIdentity();

            // If there is no identity available the user is redirected to index/index
            if (!is_array($identity)) {
                $dispatcher->forward([
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\Frontend',
                    'controller'    => 'error',
                    'action'        => 'noPermission',
                ]);
                return false;
            }

            // Check if the user have permission to the current option
            if (!isset($identity['profile']) || !$this->acl->isAllowed($identity['profile'], $controllerName, $actionName)) {
                $dispatcher->forward([
                    'module'        => 'Application',
                    'namespace'     => 'Application\Controller\Frontend',
                    'controller'    => 'error',
                    'action'        => 'noPermission',
                ]);
                return false;
            }
        }
    }

}
