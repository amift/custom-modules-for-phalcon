<?php

namespace Application\Controller\Frontend;

use Common\Controller\AbstractFrontendController;

class ApplicationController extends AbstractFrontendController
{

    /**
     * Startpage view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        /*$data = $this->cache->get('startpage-data');

        if ($data === null) {
            $contentClass = 'page-content';
            $data = compact('contentClass');
            $this->cache->save('startpage-data', $data);
        }

        $this->view->setVars($data);*/
        $this->dispatcher->forward([
            'module'        => 'Articles',
            'namespace'     => 'Articles\Controller\Frontend',
            'controller'    => 'articles',
            'action'        => 'index',
        ]);
        return false;
    }

}
