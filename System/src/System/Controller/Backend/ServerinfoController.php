<?php

namespace System\Controller\Backend;

use Common\Controller\AbstractBackendController;

class ServerinfoController extends AbstractBackendController
{

    /**
     * General info view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $conn   = $this->getEntityManager()->getConnection();
        $db     = $conn->fetchAssoc('SELECT VERSION() as dbVer');
        $tab    = 'general';
        $server = [
            'web' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '-',
            'database' => [
                'platform'  => ucfirst($conn->getDatabasePlatform()->getName()),
                'version'   => isset($db['dbVer']) ? $db['dbVer'] : ''
            ],
            'phalcon_version' => \Phalcon\Version::get(),
            'php_version' => phpversion(),
            'php_ext' => implode(', ', get_loaded_extensions())
        ];

        $this->view->setVars(compact('tab', 'server'));
    }

    /**
     * PHP info view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function phpAction()
    {
        $tab = 'php';
        $phpinfo = $this->phpInfoCms();

        $this->view->setVars(compact('tab', 'phpinfo'));
    }

    protected function phpInfoCms()
    {
        ob_start();
        phpinfo();
        $tmp = explode('phpinfo()', strip_tags(ob_get_clean(), "<table><tr><th><td><h1><h2><pre><hr>"), 2);
        $html = isset($tmp[1]) ? str_replace(' width="600"', '', $tmp[1]) : '';
        return $html;
    }

}