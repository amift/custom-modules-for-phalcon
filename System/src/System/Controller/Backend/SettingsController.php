<?php

namespace System\Controller\Backend;

use Common\Controller\AbstractBackendController;

class SettingsController extends AbstractBackendController
{

    /**
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $message = '@toDo';

        $this->view->setVars(compact('message'));
    }

}