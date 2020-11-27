<?php

namespace Application\Controller\Backend;

use Common\Controller\AbstractBackendController;

class ErrorController extends AbstractBackendController
{

    public function notFoundAction()
    {
        $this->view->setVar('message', 'Page you are looking for not found!<Br>backend ErrorController');
    }

    public function noPermissionAction()
    {
        $this->view->setVar('message', 'You don\'t have access to this page!<Br>backend ErrorController');
    }

    public function systemErrorAction()
    {
        $this->view->setVar('message', 'Page you are looking for currently unvialable!<Br>backend ErrorController');
    }

}
