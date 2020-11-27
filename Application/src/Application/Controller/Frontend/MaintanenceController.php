<?php

namespace Application\Controller\Frontend;

use Phalcon\Mvc\Controller;

class MaintanenceController extends Controller
{

    public function technicalWorksAction()
    {
        $contentClass = 'my-profile';
        $enableCoverAdd = false;
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass', 'enableCoverAdd'));
        $this->view->setLayout('maintanence');
    }

    public function comingSoonAction()
    {
        $contentClass = 'my-profile';
        $enableCoverAdd = false;
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass', 'enableCoverAdd'));
        $this->view->setLayout('maintanence');
    }

}
