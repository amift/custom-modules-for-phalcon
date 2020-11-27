<?php

namespace Application\Controller\Frontend;

use Phalcon\Mvc\Controller;

class ErrorController extends Controller
{

    public function notFoundAction()
    {
        $contentClass = 'my-profile';
        $enableCoverAdd = false;
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass', 'enableCoverAdd'));
    }

    public function noPermissionAction()
    {
        $contentClass = 'my-profile';
        $enableCoverAdd = false;
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass', 'enableCoverAdd'));
    }

    public function systemErrorAction()
    {
        $contentClass = 'my-profile';
        $enableCoverAdd = false;
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass', 'enableCoverAdd'));
    }

    public function invalidAccessAction()
    {
        $contentClass = 'my-profile';
        $enableCoverAdd = false;
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass', 'enableCoverAdd'));
    }

    public function restrictedAccessAction()
    {
        $contentClass = 'my-profile';
        $enableCoverAdd = false;
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass', 'enableCoverAdd'));
    }

}
